<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk Serper.dev (Google Search API) yang dipakai sebagai tool
 * "web_search" versi custom.
 *
 * Kenapa ini perlu (bukan cukup pakai fitur bawaan provider):
 * - Claude sudah punya web_search bawaan Anthropic (server tool), jadi tidak
 *   lewat sini -- lihat ClaudeService::toolDefinitions().
 * - Gemini punya grounding "google_search" bawaan, TAPI (per Juli 2026) tool
 *   itu tidak bisa digabung dengan functionDeclarations custom (getTrend,
 *   getGoogleTrendsNow, dst) di endpoint generateContent yang dipakai project
 *   ini -- kombinasi itu baru didukung lewat endpoint "Interactions API" yang
 *   masih Preview & cuma untuk model Gemini 3. Supaya Gemini tetap bisa
 *   browsing web sambil tetap pakai tool custom kita, "web_search" untuk
 *   Gemini dijalankan lokal lewat Serper.dev, sama seperti getGoogleTrendsNow.
 * - Groq pakai browser_search bawaan Groq sendiri (lihat GroqService), jadi
 *   TIDAK butuh Serper.
 *
 * Daftar dulu di https://serper.dev untuk dapat API key (ada free tier),
 * lalu isi SERPER_API_KEY di .env.
 */
class SerperSearchService
{
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey  = (string) config('services.serper.key', '');
        $this->timeout = (int) config('services.serper.timeout', 10);
    }

    /**
     * POST https://google.serper.dev/search
     *
     * @return array Hasil pencarian yang sudah disederhanakan (siap dikirim
     *                balik ke Gemini sebagai functionResponse), atau
     *                ['error' => true, 'message' => ...] kalau gagal.
     */
    public function search(string $query, int $numResults = 5): array
    {
        if (!$this->apiKey) {
            Log::warning('SerperSearchService: SERPER_API_KEY belum diset di .env');

            return [
                'error'   => true,
                'message' => 'SERPER_API_KEY belum dikonfigurasi, web_search tidak bisa dipakai.',
            ];
        }

        try {
            $request = Http::withHeaders([
                'X-API-KEY'    => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout);

            // Sama seperti service lain: hindari error "SSL certificate
            // problem" yang umum di setup lokal Windows/Laragon/XAMPP.
            // JANGAN pernah dimatikan di production.
            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post('https://google.serper.dev/search', [
                'q'  => $query,
                'num' => max(1, min($numResults, 10)),
                // Bias hasil ke Indonesia & Bahasa Indonesia, tim marcom-nya di sini.
                'gl' => 'id',
                'hl' => 'id',
            ]);
        } catch (\Exception $e) {
            Log::error('SerperSearchService: exception saat memanggil Serper API', [
                'message' => $e->getMessage(),
            ]);

            return [
                'error'   => true,
                'message' => 'Tidak bisa menghubungi Serper API: ' . $e->getMessage(),
            ];
        }

        if (!$response->successful()) {
            Log::warning('SerperSearchService: Serper API mengembalikan error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'error'   => true,
                'message' => 'Serper API mengembalikan error (HTTP ' . $response->status() . ').',
            ];
        }

        $data = $response->json() ?? [];

        // Sederhanakan respons Serper yang cukup gemuk (answerBox,
        // knowledgeGraph, organic, peopleAlsoAsk, relatedSearches, dst)
        // supaya hemat token saat dikirim balik ke model.
        $result = [
            'query'   => $query,
            'results' => collect($data['organic'] ?? [])
                ->take($numResults)
                ->map(fn ($item) => [
                    'title'   => $item['title'] ?? null,
                    'link'    => $item['link'] ?? null,
                    'snippet' => $item['snippet'] ?? null,
                    'date'    => $item['date'] ?? null,
                ])
                ->values()
                ->all(),
        ];

        if (isset($data['answerBox'])) {
            $result['answer_box'] = $data['answerBox']['answer']
                ?? $data['answerBox']['snippet']
                ?? null;
        }

        return $result;
    }
}
