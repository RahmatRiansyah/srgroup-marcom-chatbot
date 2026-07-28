<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Melacak status "limit" tiap engine AI (Claude, Groq, Gemini) secara terpusat.
 *
 * Dua manfaat utama:
 *  1. ChatController bisa SKIP manggil engine yang sudah diketahui kena limit,
 *     langsung lanjut ke engine berikutnya di rantai fallback -- tidak perlu
 *     nunggu request gagal/timeout dulu baru pindah.
 *  2. Model-selector di UI chat bisa nampilkan badge "Limit Tercapai" dan
 *     menonaktifkan (coret) opsi tersebut sampai batas waktunya lewat.
 *
 * Sengaja disimpan lewat Cache (bukan kolom tabel baru) karena sifatnya
 * sementara & self-expiring: begitu TTL habis, Cache::has() otomatis balik
 * false lagi tanpa perlu job/cron pembersih terpisah.
 */
class EngineStatusService
{
    public const ENGINES = ['claude', 'groq', 'gemini'];

    /**
     * TTL default (detik) kalau API tidak memberi tahu Retry-After & ini
     * bukan kasus "kuota/kredit habis". null berarti "anggap reset tengah
     * malam" -- dipakai untuk Gemini yang kuota gratisnya harian.
     */
    protected const DEFAULT_TTL_SECONDS = [
        'claude' => 300,  // rate limit per-menit Anthropic, biasanya reset cepat
        'groq'   => 120,  // kuota gratis Groq relatif lega, reset per menit
        'gemini' => null, // kuota gratis Gemini ~20 request/hari -> reset harian
    ];

    /** Kalau alasannya "kuota/kredit habis" (bukan sekadar rate limit sesaat), pakai TTL lebih panjang. */
    protected const QUOTA_TTL_SECONDS = 6 * 60 * 60; // 6 jam

    protected function key(string $engine): string
    {
        return "engine_limit:{$engine}";
    }

    /**
     * Tandai satu engine sedang limit / tidak bisa dipakai sementara.
     *
     * @param string $engine 'claude' | 'groq' | 'gemini'
     * @param int|null $retryAfterSeconds dari header Retry-After API kalau tersedia
     * @param string|null $reason 'rate_limit' | 'quota_habis' | dll, ditampilkan di UI
     */
    public function markLimited(string $engine, ?int $retryAfterSeconds = null, ?string $reason = null): void
    {
        if (!in_array($engine, self::ENGINES, true)) {
            return;
        }

        $until = $retryAfterSeconds
            ? now()->addSeconds(max($retryAfterSeconds, 5))
            : $this->defaultUntil($engine, $reason);

        Cache::put($this->key($engine), [
            'reason' => $reason ?? 'rate_limit',
            'until'  => $until->toIso8601String(),
        ], $until);
    }

    protected function defaultUntil(string $engine, ?string $reason): Carbon
    {
        if ($reason === 'quota_habis') {
            return now()->addSeconds(self::QUOTA_TTL_SECONDS);
        }

        $ttl = self::DEFAULT_TTL_SECONDS[$engine] ?? 300;

        if ($ttl === null) {
            // Kuota harian -> anggap reset tengah malam waktu server.
            return now()->endOfDay()->addSecond();
        }

        return now()->addSeconds($ttl);
    }

    /** Hapus status limit lebih awal (kalau ternyata request berikutnya sukses). */
    public function clear(string $engine): void
    {
        Cache::forget($this->key($engine));
    }

    public function isLimited(string $engine): bool
    {
        return Cache::has($this->key($engine));
    }

    /** Status semua engine sekaligus -- dipakai model-selector di UI chat. */
    public function status(): array
    {
        $result = [];

        foreach (self::ENGINES as $engine) {
            $data = Cache::get($this->key($engine));

            $result[$engine] = [
                'limited' => $data !== null,
                'until'   => $data['until'] ?? null,
                'reason'  => $data['reason'] ?? null,
            ];
        }

        return $result;
    }
}
