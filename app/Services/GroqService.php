<?php

namespace App\Services;

use App\Services\Concerns\UsesAnalyticsTools;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk Groq (GroqCloud) dengan tool use / function calling.
 *
 * Ini adalah engine FALLBACK KEDUA di rantai: ChatController panggil Claude
 * dulu (engine utama) -> kalau gagal, ke sini (Groq) -> kalau Groq juga
 * gagal, baru ke Gemini. Groq dipilih sebagai fallback pertama (sebelum
 * Gemini) karena kuota gratisnya jauh lebih lega (puluhan request/menit,
 * ribuan/hari) dibanding kuota gratis Gemini yang cuma ~20 request/hari.
 *
 * API Groq sengaja dipakai lewat endpoint OpenAI-compatible-nya
 * (https://api.groq.com/openai/v1/chat/completions), jadi format tool
 * calling-nya sama seperti OpenAI: tools -> tool_calls di response ->
 * balas dengan message role "tool" per tool_call_id. Ini BEDA formatnya
 * dari Claude (top-level "tools" + block tool_use/tool_result) maupun
 * Gemini (functionDeclarations + functionCall/functionResponse), jadi
 * konversinya ditangani sendiri di sini, bukan lewat trait.
 *
 * Soal "web_search": dipakai lewat tool bawaan Groq sendiri, "browser_search"
 * (lihat toolDefinitions()) -- dieksekusi di server Groq, jadi TIDAK lewat
 * runAnalyticsTool()/AnalyticsApiService seperti getTrend dkk, dan hasilnya
 * langsung nyatu di jawaban akhir (bukan tool_calls yang perlu kita balas
 * manual). Tool ini CUMA didukung di model GPT-OSS (bukan di
 * llama-3.3-70b-versatile), makanya model default di bawah diganti ke
 * openai/gpt-oss-120b -- salah satu dari sedikit model Groq yang boleh
 * gabung browser_search bawaan dengan tool custom kita di request yang sama
 * (groq/compound TIDAK bisa, lihat dokumentasi Groq: "custom user-provided
 * tools are not supported at this time" untuk compound systems).
 */
class GroqService
{
    use UsesAnalyticsTools;

    protected string $apiKey;
    protected string $model;

    /** Batas berapa kali boleh bolak-balik minta tool sebelum dipaksa berhenti. */
    protected int $maxToolRounds = 4;

    public function __construct(protected AnalyticsApiService $analytics)
    {
        $this->apiKey = (string) config('services.groq.key', '');
        $this->model  = (string) config('services.groq.model', 'openai/gpt-oss-120b');
    }

    /**
     * @param array<int, array{role: string, content: mixed}> $history format sama seperti ClaudeService (role: user/assistant)
     * @param string $userMessage
     * @return array{reply: string, tool_calls: array, error: bool}
     */
    public function chat(array $history, string $userMessage): array
    {
        if (!$this->apiKey) {
            Log::warning('GroqService: GROQ_API_KEY belum diset di .env');

            return [
                'reply'      => 'GROQ_API_KEY belum dikonfigurasi.',
                'tool_calls' => [],
                'error'      => true,
            ];
        }

        // Format OpenAI-compatible: system message jadi bagian dari array
        // messages (bukan parameter terpisah seperti di Claude/Gemini).
        $messages = [
            ['role' => 'system', 'content' => $this->analyticsSystemPrompt()],
        ];

        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => (string) $turn['content']];
        }
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

            $message    = $response['data']['choices'][0]['message'] ?? [];
            $toolCalls  = $message['tool_calls'] ?? [];

            // Tidak ada tool call -> ambil teks jawaban akhir.
            if (empty($toolCalls)) {
                $text = trim((string) ($message['content'] ?? ''));

                return [
                    'reply'      => $text !== '' ? $text : 'Maaf, tidak ada jawaban yang bisa ditampilkan.',
                    'tool_calls' => $toolCallsLog,
                    'error'      => false,
                ];
            }

            // Groq minta menjalankan satu/lebih tool -> simpan giliran assistant
            // apa adanya (termasuk tool_calls-nya), lalu balas tiap tool_call_id
            // dengan message role "tool" berisi hasilnya.
            $messages[] = [
                'role'       => 'assistant',
                'content'    => $message['content'] ?? null,
                'tool_calls' => $toolCalls,
            ];

            foreach ($toolCalls as $call) {
                $toolName  = $call['function']['name'] ?? '';
                $rawArgs   = $call['function']['arguments'] ?? '{}';
                $toolInput = json_decode($rawArgs, true) ?: [];
                $result    = $this->runAnalyticsTool($toolName, $toolInput);

                $toolCallsLog[] = [
                    'name'   => $toolName,
                    'input'  => $toolInput,
                    'result' => $result,
                ];

                $messages[] = [
                    'role'         => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'content'      => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }
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
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(45); // browser_search bawaan Groq butuh waktu ekstra (search + baca halaman) sebelum balas

            // Sama seperti ClaudeService/GeminiService: hindari error "SSL
            // certificate problem" yang umum di setup lokal Windows/Laragon/XAMPP.
            // JANGAN pernah dimatikan di production.
            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => $this->model,
                'max_tokens'  => 1024,
                'messages'    => $messages,
                'tools'       => $this->toolDefinitions(),
                'tool_choice' => 'auto',
            ]);
        } catch (\Exception $e) {
            Log::error('GroqService: exception saat memanggil Groq API', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => true, 'message' => 'Gagal menghubungi Groq API: ' . $e->getMessage()];
        }

        if (!$response->successful()) {
            // Termasuk kasus rate limit (HTTP 429) dan model/koneksi bermasalah --
            // ditandai error supaya ChatController lanjut fallback ke Gemini.
            Log::warning('GroqService: Groq API mengembalikan error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'error'   => true,
                'message' => 'Groq API mengembalikan error (HTTP ' . $response->status() . ').',
            ];
        }

        return ['error' => false, 'data' => $response->json()];
    }

    /**
     * Konversi skema tool generik dari trait ke format OpenAI-compatible Groq
     * (function), DITAMBAH tool bawaan Groq "browser_search" di baris
     * terakhir -- sama seperti web_search bawaan Anthropic di ClaudeService,
     * ini dieksekusi Groq sendiri, bukan kita (lihat docblock class).
     */
    protected function toolDefinitions(): array
    {
        $customTools = collect($this->toolSchemas())->map(fn ($tool) => [
            'type'     => 'function',
            'function' => [
                'name'        => $tool['name'],
                'description' => $tool['description'],
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => $tool['properties'],
                    'required'   => $tool['required'],
                ],
            ],
        ])->values()->all();

        return [
            ...$customTools,
            ['type' => 'browser_search'],
        ];
    }
}
