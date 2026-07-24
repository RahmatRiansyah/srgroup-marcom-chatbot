<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk memanggil mesin analisis Python (FastAPI).
 *
 * Ini yang dipakai ChatController / LLM function-calling untuk mengambil
 * data tren & kompetitor, alih-alih query database Laravel secara langsung.
 * Endpoint & API key diatur lewat config/services.php -> ANALYTICS_API_URL
 * dan ANALYTICS_API_KEY di .env.
 */
class AnalyticsApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.analytics.url', 'http://127.0.0.1:8000'), '/');
        $this->apiKey = config('services.analytics.key', '');
        $this->timeout = (int) config('services.analytics.timeout', 10);
    }

    protected function client()
    {
        return Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->timeout($this->timeout);
    }

    /**
     * GET /trends?keyword=...&days=...
     * Cari postingan/tren yang relevan dengan sebuah keyword.
     *
     * $days membatasi hasil hanya postingan N hari terakhir (default 30) --
     * sengaja tidak dibiarkan tanpa batas, supaya chatbot tidak pernah
     * menyajikan data lama sebagai "tren sekarang". Endpoint akan
     * mengembalikan newest_post_age_days di response supaya kesegaran
     * datanya bisa disebutkan eksplisit ke user.
     */
    public function getTrend(?string $keyword = null, int $limit = 20, int $days = 30): array
    {
        return $this->get('/trends', [
            'keyword' => $keyword,
            'limit'   => $limit,
            'days'    => $days,
        ]);
    }

    /**
     * GET /competitor/{nama}
     * Detail satu kompetitor/target + postingan terbarunya.
     */
    public function getCompetitorPrice(string $nama, int $limit = 10): array
    {
        return $this->get("/competitor/" . rawurlencode($nama), [
            'limit' => $limit,
        ]);
    }

    /**
     * GET /summary?days=1
     * Ringkasan aktivitas & postingan terbaru lintas semua kompetitor.
     */
    public function getSummary(int $days = 1): array
    {
        return $this->get('/summary', [
            'days' => $days,
        ]);
    }

    /**
     * GET /trends/live?keyword=...&geo=ID
     * Query LANGSUNG ke Google Trends (bukan data lama hasil scraping).
     * Dipakai kalau keyword/topiknya belum tentu ada di antara
     * kompetitor/target yang sudah terdaftar di trend_sources.
     */
    public function getGoogleTrendsNow(string $keyword, string $geo = 'ID'): array
    {
        return $this->get('/trends/live', [
            'keyword' => $keyword,
            'geo'     => $geo,
        ]);
    }

    /**
     * POST /scrape/run
     * Jalankan scraping untuk semua target aktif sekarang juga. Dipakai oleh
     * scheduler harian (php artisan scrape:run) maupun tombol manual di admin panel.
     */
    public function triggerScrape(): array
    {
        try {
            // Timeout lebih panjang dari request GET biasa: scraping beberapa
            // target satu-satu bisa makan waktu lebih dari 10 detik.
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->timeout(120)
                ->post($this->baseUrl . '/scrape/run');

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('AnalyticsApiService: trigger scrape gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'error'   => true,
                'status'  => $response->status(),
                'message' => 'Mesin analisis mengembalikan error saat trigger scrape.',
            ];
        } catch (\Exception $e) {
            Log::error('AnalyticsApiService: exception saat trigger scrape', [
                'message' => $e->getMessage(),
            ]);

            return [
                'error'   => true,
                'message' => 'Tidak bisa menghubungi mesin analisis: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper GET generik dengan error handling yang konsisten.
     */
    protected function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client()->get($this->baseUrl . $path, array_filter($query, fn ($v) => $v !== null));

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('AnalyticsApiService: request gagal', [
                'path'   => $path,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'error'  => true,
                'status' => $response->status(),
                'message' => 'Mesin analisis mengembalikan error.',
            ];
        } catch (\Exception $e) {
            Log::error('AnalyticsApiService: exception saat memanggil ' . $path, [
                'message' => $e->getMessage(),
            ]);

            return [
                'error'   => true,
                'message' => 'Tidak bisa menghubungi mesin analisis: ' . $e->getMessage(),
            ];
        }
    }
}