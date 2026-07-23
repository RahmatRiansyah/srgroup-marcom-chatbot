<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk Tavily (https://tavily.com) yang dipakai sebagai tool
 * "web_search" versi custom untuk GeminiService.
 *
 * Kenapa Tavily (bukan Serper.dev): free tier Tavily 1.000 kredit/bulan
 * TANPA kartu kredit, dan dari awal memang didesain untuk agen AI --
 * hasilnya sudah diringkas/terstruktur, bukan HTML mentah hasil scraping,
 * jadi lebih hemat token saat dikirim balik ke model. Daftar di
 * https://tavily.com (email/Google/GitHub), API key ada langsung di
 * dashboard setelah login, formatnya "tvly-xxxxxxxx".
 *
 * Kalau nanti mau balik pakai Serper.dev, SerperSearchService.php masih ada
 * di folder ini -- tinggal ganti type-hint di constructor GeminiService.
 */
class TavilySearchService
{
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey  = (string) config('services.tavily.key', '');
        $this->timeout = (int) config('services.tavily.timeout', 10);
    }

    /**
     * POST https://api.tavily.com/search
     *
     * @return array Hasil pencarian yang sudah disederhanakan (siap dikirim
     *                balik ke Gemini sebagai functionResponse), atau
     *                ['error' => true, 'message' => ...] kalau gagal.
     */
    public function search(string $query, int $numResults = 5): array
    {
        if (!$this->apiKey) {
            Log::warning('TavilySearchService: TAVILY_API_KEY belum diset di .env');

            return [
                'error'   => true,
                'message' => 'TAVILY_API_KEY belum dikonfigurasi, web_search tidak bisa dipakai.',
            ];
        }

        try {
            $request = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout($this->timeout);

            // Sama seperti service lain: hindari error "SSL certificate
            // problem" yang umum di setup lokal Windows/Laragon/XAMPP.
            // JANGAN pernah dimatikan di production.
            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post('https://api.tavily.com/search', [
                'query'          => $query,
                'search_depth'   => 'basic', // "advanced" makan 2x kredit, tidak perlu untuk kebutuhan marcom sehari-hari
                'max_results'    => max(1, min($numResults, 10)),
                'include_answer' => true, // minta Tavily kasih ringkasan jawaban langsung, bukan cuma daftar link
                'country'        => 'indonesia',
            ]);
        } catch (\Exception $e) {
            Log::error('TavilySearchService: exception saat memanggil Tavily API', [
                'message' => $e->getMessage(),
            ]);

            return [
                'error'   => true,
                'message' => 'Tidak bisa menghubungi Tavily API: ' . $e->getMessage(),
            ];
        }

        if (!$response->successful()) {
            Log::warning('TavilySearchService: Tavily API mengembalikan error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'error'   => true,
                'message' => 'Tavily API mengembalikan error (HTTP ' . $response->status() . ').',
            ];
        }

        $data = $response->json() ?? [];

        // Sederhanakan respons Tavily supaya hemat token saat dikirim balik ke model.
        $result = [
            'query'   => $query,
            'results' => collect($data['results'] ?? [])
                ->take($numResults)
                ->map(fn ($item) => [
                    'title'   => $item['title'] ?? null,
                    'link'    => $item['url'] ?? null,
                    'snippet' => $item['content'] ?? null,
                    'date'    => $item['published_date'] ?? null,
                ])
                ->values()
                ->all(),
        ];

        if (!empty($data['answer'])) {
            $result['answer_box'] = $data['answer'];
        }

        return $result;
    }
}
