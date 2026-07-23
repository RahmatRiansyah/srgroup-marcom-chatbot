<?php

namespace App\Services;

use App\Services\Concerns\UsesAnalyticsTools;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk Claude (Anthropic Messages API) dengan tool use / function calling.
 *
 * Alur sesuai roadmap Minggu 5 ("Integrasi LLM + Function Calling"):
 *   user tanya -> Claude memutuskan lewat tool use apakah perlu data real-time
 *   -> kalau perlu, jalankan fungsi lokal (getTrend/getCompetitorPrice/getSummary)
 *   -> fungsi itu memanggil AnalyticsApiService, yang di baliknya HTTP call ke
 *      mesin analisis Python (FastAPI) -> hasilnya dikirim balik ke Claude
 *   -> Claude susun jawaban akhir dalam Bahasa Indonesia untuk tim marketing.
 *
 * Ini adalah engine UTAMA. Kalau method chat() mengembalikan error => true,
 * ChatController akan otomatis fallback ke GeminiService (mis. kredit/token
 * Claude habis, rate limit, atau service Anthropic sedang down).
 */
class ClaudeService
{
    use UsesAnalyticsTools;

    protected string $apiKey;
    protected string $model;

    /** Batas berapa kali boleh bolak-balik minta tool sebelum dipaksa berhenti. */
    protected int $maxToolRounds = 4;

    public function __construct(protected AnalyticsApiService $analytics)
    {
        $this->apiKey = (string) config('services.anthropic.key', '');
        $this->model  = (string) config('services.anthropic.model', 'claude-sonnet-5');
    }

    /**
     * @param array<int, array{role: string, content: mixed}> $history riwayat percakapan sebelumnya
     * @param string $userMessage pesan terbaru dari user
     * @return array{reply: string, tool_calls: array, error: bool}
     */
    public function chat(array $history, string $userMessage): array
    {
        if (!$this->apiKey) {
            Log::warning('ClaudeService: ANTHROPIC_API_KEY belum diset di .env');

            return [
                'reply'      => 'ANTHROPIC_API_KEY belum dikonfigurasi.',
                'tool_calls' => [],
                'error'      => true,
            ];
        }

        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $toolCallsLog = [];

        for ($round = 0; $round < $this->maxToolRounds; $round++) {
            $response = $this->callApi($messages);

            if ($response['error']) {
                return [
                    'reply'      => $response['message'],
                    'tool_calls' => $toolCallsLog,
                    'error'      => true,
                ];
            }

            $data       = $response['data'];
            $content    = $data['content'] ?? [];
            $stopReason = $data['stop_reason'] ?? null;

            // Claude sudah selesai (tidak minta tool lagi) -> ambil teks jawabannya.
            if ($stopReason !== 'tool_use') {
                $text = collect($content)
                    ->where('type', 'text')
                    ->pluck('text')
                    ->implode("\n");

                return [
                    'reply'      => $text !== '' ? $text : 'Maaf, tidak ada jawaban yang bisa ditampilkan.',
                    'tool_calls' => $toolCallsLog,
                    'error'      => false,
                ];
            }

            // Claude minta menjalankan satu/lebih tool -> jalankan lokal, lalu
            // kirim balik hasilnya sebagai tool_result di giliran berikutnya.
            $messages[] = ['role' => 'assistant', 'content' => $content];

            $toolResults = [];
            foreach ($content as $block) {
                if (($block['type'] ?? null) !== 'tool_use') {
                    continue;
                }

                $toolName  = $block['name'] ?? '';
                $toolInput = $block['input'] ?? [];
                $result    = $this->runAnalyticsTool($toolName, $toolInput);

                $toolCallsLog[] = [
                    'name'   => $toolName,
                    'input'  => $toolInput,
                    'result' => $result,
                ];

                $toolResults[] = [
                    'type'        => 'tool_result',
                    'tool_use_id' => $block['id'] ?? '',
                    'content'     => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }

            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }

        return [
            'reply'      => 'Maaf, proses pengambilan data butuh terlalu banyak langkah. Coba pertanyaan yang lebih spesifik.',
            'tool_calls' => $toolCallsLog,
            'error'      => false, // ini bukan kegagalan API, jadi tidak perlu fallback ke Gemini
        ];
    }

    /**
     * @param array $messages
     * @return array{error: bool, message?: string, data?: array}
     */
    protected function callApi(array $messages): array
    {
        try {
            $request = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30);

            // Banyak setup lokal (Laragon/XAMPP di Windows) belum punya CA
            // certificate bundle yang benar untuk PHP curl, jadi request HTTPS
            // keluar gagal dengan "SSL certificate problem". Ini AMAN dimatikan
            // di lokal, tapi JANGAN pernah dimatikan di production.
            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->model,
                'max_tokens' => 1024,
                'system'     => $this->analyticsSystemPrompt(),
                'tools'      => $this->toolDefinitions(),
                'messages'   => $messages,
            ]);
        } catch (\Exception $e) {
            Log::error('ClaudeService: exception saat memanggil Claude API', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => true, 'message' => 'Gagal menghubungi Claude API: ' . $e->getMessage()];
        }

        if (!$response->successful()) {
            // Termasuk kasus token/kredit habis (biasanya HTTP 400 "credit balance too low")
            // dan rate limit (HTTP 429) -- keduanya ditandai error supaya ChatController fallback ke Gemini.
            Log::warning('ClaudeService: Claude API mengembalikan error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'error'   => true,
                'message' => 'Claude API mengembalikan error (HTTP ' . $response->status() . ').',
            ];
        }

        return ['error' => false, 'data' => $response->json()];
    }

    /**
     * Konversi skema tool generik dari trait ke format Anthropic (input_schema),
     * DITAMBAH server tool bawaan Anthropic "web_search" di baris terakhir.
     *
     * web_search ini beda dari getTrend/getCompetitorPrice/getSummary/
     * getGoogleTrendsNow: bukan kita yang jalankan (bukan lewat
     * runAnalyticsTool), tapi dieksekusi langsung oleh Anthropic di sisi
     * mereka -- Claude bisa search web umum & kasih jawaban dengan sitasi
     * sumber. Ini kenapa tool ini HANYA ditambahkan di sini (ClaudeService),
     * bukan di trait UsesAnalyticsTools: Groq (format OpenAI) dan Gemini
     * (format functionDeclarations) tidak mengenal tipe tool ini.
     */
    protected function toolDefinitions(): array
    {
        $customTools = collect($this->toolSchemas())->map(fn ($tool) => [
            'name'        => $tool['name'],
            'description' => $tool['description'],
            'input_schema' => [
                'type'       => 'object',
                'properties' => $tool['properties'],
                'required'   => $tool['required'],
            ],
        ])->values()->all();

        return [
            ...$customTools,
            [
                'type'     => 'web_search_20250305',
                'name'     => 'web_search',
                // Batasi maksimal 3x panggilan web_search per pesan user,
                // biar biaya nggak membengkak kalau Claude "kebablasan" cari.
                'max_uses' => 3,
            ],
        ];
    }
}