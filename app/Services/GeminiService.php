<?php

namespace App\Services;

use App\Services\Concerns\UsesAnalyticsTools;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk Gemini (Google Generative Language API) dengan function calling.
 *
 * Ini adalah engine CADANGAN (fallback): ChatController memanggil ClaudeService
 * dulu, dan hanya lempar ke sini kalau Claude gagal (kredit/token habis, rate
 * limit, atau Anthropic sedang down). Sengaja dibuat pakai tool-calling asli
 * Gemini (bukan dump context mentah ke prompt) supaya kualitas & gaya jawaban
 * tetap konsisten dengan ClaudeService, walau yang menjawab beda model.
 */
class GeminiService
{
    use UsesAnalyticsTools;

    protected string $apiKey;

    /** Model utama dari .env, plus urutan fallback antar-model Gemini kalau model utama tidak tersedia/limit. */
    protected array $modelCandidates;

    protected int $maxToolRounds = 4;

    public function __construct(protected AnalyticsApiService $analytics)
    {
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

                $result = $this->runAnalyticsTool($name, $args);

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

                $lastError = 'Gemini API mengembalikan error (HTTP ' . $response->status() . ') pada model ' . $model . '.';

                Log::warning('GeminiService: request gagal, coba model berikutnya', [
                    'model'  => $model,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            } catch (\Exception $e) {
                $lastError = 'Gagal menghubungi Gemini API: ' . $e->getMessage();

                Log::error('GeminiService: exception saat memanggil Gemini API', [
                    'model'   => $model,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return ['error' => true, 'message' => $lastError];
    }

    /** Konversi skema tool generik dari trait ke format Gemini (functionDeclarations). */
    protected function toolDefinitions(): array
    {
        return collect($this->toolSchemas())->map(fn ($tool) => [
            'name'        => $tool['name'],
            'description' => $tool['description'],
            'parameters'  => [
                'type'       => 'object',
                'properties' => $tool['properties'],
                'required'   => $tool['required'],
            ],
        ])->values()->all();
    }
}
