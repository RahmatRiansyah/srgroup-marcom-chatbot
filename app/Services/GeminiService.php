<?php

namespace App\Services;

use App\Services\Concerns\UsesAnalyticsTools;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Client untuk Gemini (Google Generative Language API) dengan function calling.
 *
 * Ini adalah engine CADANGAN (fallback): ChatController memanggil ClaudeService
 * dulu, dan hanya lempar ke sini kalau Claude gagal (kredit/token habis, rate
 * limit, atau Anthropic sedang down). Sengaja dibuat pakai tool-calling asli
 * Gemini (bukan dump context mentah ke prompt) supaya kualitas & gaya jawaban
 * tetap konsisten dengan ClaudeService, walau yang menjawab beda model.
 *
 * Soal "web_search": Gemini punya grounding "google_search" bawaan, tapi
 * (per Juli 2026) tool itu TIDAK BISA digabung dengan functionDeclarations
 * custom (getTrend, getGoogleTrendsNow, dst) di endpoint generateContent yang
 * dipakai di sini -- kombinasi built-in + custom tool baru didukung lewat
 * endpoint "Interactions API" yang masih Preview & cuma untuk model Gemini 3.
 * Supaya tetap konsisten & jalan di semua model kandidat, "web_search" untuk
 * Gemini dijalankan LOKAL lewat TavilySearchService (tavily.com), persis
 * seperti getGoogleTrendsNow -- lihat runTool() & toolDefinitions() di bawah.
 */
class GeminiService
{
    use UsesAnalyticsTools;

    protected string $apiKey;

    /** Model utama dari .env, plus urutan fallback antar-model Gemini kalau model utama tidak tersedia/limit. */
    protected array $modelCandidates;

    protected int $maxToolRounds = 4;

    public function __construct(
        protected AnalyticsApiService $analytics,
        protected TavilySearchService $webSearch,
        protected EngineStatusService $engineStatus,
    ) {
        $this->apiKey = (string) config('services.gemini.key', '');

        $primaryModel = (string) config('services.gemini.model', 'gemini-2.5-flash');
        $fallbackModels = ['gemini-3.5-flash', 'gemini-3.1-flash-lite'];

        $this->modelCandidates = array_values(array_unique(array_merge([$primaryModel], $fallbackModels)));
    }

    /**
     * @param array<int, array{role: string, content: mixed}> $history format sama seperti ClaudeService (role: user/assistant)
     * @param string $userMessage
     * @return array{reply: string, tool_calls: array, error: bool}
     */
    public function chat(array $history, string $userMessage): array
    {
        if (!$this->apiKey) {
            Log::warning('GeminiService: GEMINI_API_KEY belum diset di .env');

            return [
                'reply'      => 'GEMINI_API_KEY belum dikonfigurasi.',
                'tool_calls' => [],
                'error'      => true,
            ];
        }

        if ($this->engineStatus->isLimited('gemini')) {
            return [
                'reply'      => 'Gemini sedang mencapai batas penggunaan (limit). Coba model lain atau tunggu beberapa saat.',
                'tool_calls' => [],
                'error'      => true,
            ];
        }

        // Konversi format history Claude (role user/assistant, content string)
        // ke format Gemini (role user/model, parts: [{text}]).
        $contents = [];
        foreach ($history as $turn) {
            $contents[] = [
                'role'  => $turn['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => (string) $turn['content']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        $toolCallsLog = [];

        for ($round = 0; $round < $this->maxToolRounds; $round++) {
            $response = $this->callApi($contents);

            if ($response['error']) {
                return [
                    'reply'      => $response['message'],
                    'tool_calls' => $toolCallsLog,
                    'error'      => true,
                ];
            }

            $parts = $response['data']['candidates'][0]['content']['parts'] ?? [];

            $functionCalls = array_values(array_filter($parts, fn ($p) => isset($p['functionCall'])));

            // Tidak ada function call -> ambil teks jawaban akhir.
            if (empty($functionCalls)) {
                $text = collect($parts)->pluck('text')->filter()->implode("\n");

                return [
                    'reply'      => $text !== '' ? $text : 'Maaf, tidak ada jawaban yang bisa ditampilkan.',
                    'tool_calls' => $toolCallsLog,
                    'error'      => false,
                ];
            }

            // Gemini minta function call -> simpan giliran model apa adanya, jalankan tiap tool,
            // lalu kirim balik semua functionResponse dalam satu giliran user.
            $contents[] = ['role' => 'model', 'parts' => $parts];

            $responseParts = [];
            foreach ($functionCalls as $part) {
                $call = $part['functionCall'];
                $name = $call['name'] ?? '';
                $args = $call['args'] ?? [];

                $result = $this->runTool($name, $args);

                $toolCallsLog[] = ['name' => $name, 'input' => $args, 'result' => $result];

                $functionResponse = [
                    'name'     => $name,
                    'response' => $result,
                ];
                if (isset($call['id'])) {
                    $functionResponse['id'] = $call['id'];
                }

                $responseParts[] = ['functionResponse' => $functionResponse];
            }

            $contents[] = ['role' => 'user', 'parts' => $responseParts];
        }

        return [
            'reply'      => 'Maaf, proses pengambilan data butuh terlalu banyak langkah. Coba pertanyaan yang lebih spesifik.',
            'tool_calls' => $toolCallsLog,
            'error'      => false,
        ];
    }

    /**
     * Coba tiap model kandidat berurutan (berguna kalau satu model kena limit/tidak tersedia).
     *
     * @return array{error: bool, message?: string, data?: array}
     */
    protected function callApi(array $contents): array
    {
        $lastError = 'Tidak ada model Gemini yang berhasil dihubungi.';

        // Dipakai untuk menentukan apakah SEMUA model kandidat gagal karena
        // limit/kuota (bukan sekadar error koneksi/model tertentu) -- kalau
        // iya, seluruh engine "gemini" ditandai limit di EngineStatusService,
        // bukan cuma satu model kandidatnya saja.
        $allFailuresAreQuotaIssues = true;
        $lastRetryAfter = null;
        $lastQuotaReason = 'rate_limit';

        foreach ($this->modelCandidates as $model) {
            try {
                $request = Http::withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'content-type'   => 'application/json',
                ])->timeout(30);

                // Sama seperti ClaudeService: hindari error "SSL certificate
                // problem" yang umum di setup lokal Windows/Laragon/XAMPP.
                // JANGAN pernah dimatikan di production.
                if (app()->environment('local')) {
                    $request = $request->withoutVerifying();
                }

                $response = $request->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    [
                        'system_instruction' => ['parts' => [['text' => $this->analyticsSystemPrompt()]]],
                        'contents'           => $contents,
                        'tools'              => [['functionDeclarations' => $this->toolDefinitions()]],
                    ]
                );

                if ($response->successful()) {
                    return ['error' => false, 'data' => $response->json()];
                }

                $status = $response->status();
                $body   = $response->body();

                $isQuotaIssue = $status === 429
                    || ($status === 400 && Str::contains(strtolower($body), ['credit', 'quota', 'insufficient']));

                if (!$isQuotaIssue) {
                    $allFailuresAreQuotaIssues = false;
                } else {
                    $lastQuotaReason = $status === 429 ? 'rate_limit' : 'quota_habis';
                    $retryAfter = $response->header('retry-after');
                    $lastRetryAfter = $retryAfter ? (int) $retryAfter : $lastRetryAfter;
                }

                $lastError = 'Gemini API mengembalikan error (HTTP ' . $status . ') pada model ' . $model . '.';

                Log::warning('GeminiService: request gagal, coba model berikutnya', [
                    'model'  => $model,
                    'status' => $status,
                    'body'   => $body,
                ]);
            } catch (\Exception $e) {
                $allFailuresAreQuotaIssues = false;
                $lastError = 'Gagal menghubungi Gemini API: ' . $e->getMessage();

                Log::error('GeminiService: exception saat memanggil Gemini API', [
                    'model'   => $model,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        // Semua model kandidat sudah dicoba dan semuanya gagal karena limit/kuota
        // -> tandai seluruh engine "gemini" limit sementara, supaya model-selector
        // di UI langsung menonaktifkannya & ChatController tidak perlu coba lagi.
        if ($allFailuresAreQuotaIssues) {
            $this->engineStatus->markLimited('gemini', $lastRetryAfter, $lastQuotaReason);
        }

        return ['error' => true, 'message' => $lastError];
    }

    /**
     * Jalankan satu tool call, baik itu tool analitik dari trait (getTrend,
     * getCompetitorPrice, getSummary, getGoogleTrendsNow) maupun "web_search"
     * yang khusus di-handle di sini lewat TavilySearchService (lihat
     * penjelasan di docblock class).
     */
    protected function runTool(string $name, array $args): array
    {
        if ($name === 'web_search') {
            return $this->webSearch->search(
                $args['query'] ?? '',
                (int) ($args['num_results'] ?? 5)
            );
        }

        return $this->runAnalyticsTool($name, $args);
    }

    /**
     * Konversi skema tool generik dari trait ke format Gemini
     * (functionDeclarations), DITAMBAH definisi "web_search" di baris
     * terakhir -- beda dari 4 tool trait lainnya, tool ini dieksekusi lewat
     * TavilySearchService (runTool()), bukan runAnalyticsTool()/AnalyticsApiService.
     */
    protected function toolDefinitions(): array
    {
        $analyticsTools = collect($this->toolSchemas())->map(fn ($tool) => [
            'name'        => $tool['name'],
            'description' => $tool['description'],
            'parameters'  => [
                'type'       => 'object',
                'properties' => $tool['properties'],
                'required'   => $tool['required'],
            ],
        ])->values()->all();

        return [
            ...$analyticsTools,
            [
                'name'        => 'web_search',
                'description' => 'Cari di web umum (Google Search) untuk berita/tren terkini yang di luar cakupan Google Trends maupun data internal, mis. kompetitor baru atau isu yang lagi ramai dibicarakan.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'query'       => ['type' => 'string', 'description' => "Kata kunci pencarian, misal 'kompetitor baru bubble tea Jakarta 2026'"],
                        'num_results' => ['type' => 'integer', 'description' => 'Jumlah maksimal hasil, default 5, maksimal 10'],
                    ],
                    'required' => ['query'],
                ],
            ],
        ];
    }
}
