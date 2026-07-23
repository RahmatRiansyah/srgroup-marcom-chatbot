<?php

namespace App\Services\Concerns;

/**
 * Logic yang dipakai bersama oleh ClaudeService & GeminiService: definisi
 * tool getTrend/getCompetitorPrice/getSummary, cara menjalankannya lewat
 * AnalyticsApiService, dan system prompt-nya. Disatukan di sini supaya
 * jawaban chatbot konsisten mau dijawab Claude atau Gemini (fallback).
 *
 * Class yang pakai trait ini WAJIB punya property:
 *   protected AnalyticsApiService $analytics;
 */
trait UsesAnalyticsTools
{
    protected function runAnalyticsTool(string $name, array $input): array
    {
        return match ($name) {
            'getTrend' => $this->analytics->getTrend(
                $input['keyword'] ?? null,
                (int) ($input['limit'] ?? 20)
            ),
            'getCompetitorPrice' => $this->analytics->getCompetitorPrice(
                $input['nama'] ?? '',
                (int) ($input['limit'] ?? 10)
            ),
            'getSummary' => $this->analytics->getSummary(
                (int) ($input['days'] ?? 1)
            ),
            default => [
                'error'   => true,
                'message' => "Tool '{$name}' tidak dikenal.",
            ],
        };
    }

    protected function analyticsSystemPrompt(): string
    {
        return <<<TEXT
Kamu adalah Asisten Strategi Marketing & Komunikasi (Marcom) untuk tim internal.

Tugasmu membantu tim menjawab pertanyaan seputar tren pasar dan aktivitas kompetitor,
berdasarkan data yang benar-benar dipantau sistem — jangan pernah mengarang data.

Kamu punya 3 tools untuk mengambil data real-time dari mesin analisis. SELALU pakai
tool yang sesuai dulu sebelum menjawab pertanyaan yang butuh data spesifik:
- getTrend: cari postingan/tren berdasarkan kata kunci (mis. "apa yang lagi rame soal diskon")
- getCompetitorPrice: detail & postingan terbaru satu kompetitor/target tertentu (butuh nama)
- getSummary: ringkasan aktivitas semua kompetitor/target dalam N hari terakhir

Kalau hasil tool kosong atau error, katakan terus terang datanya belum tersedia — jangan
menebak-nebak. Untuk sapaan atau pertanyaan strategi umum yang tidak butuh data spesifik,
jawab langsung tanpa tools. Selalu jawab dalam Bahasa Indonesia: singkat, jelas, dan
actionable untuk tim marketing.
TEXT;
    }

    /** Skema 3 tools, format generik (dipakai ulang oleh masing-masing service dengan penyesuaian key). */
    protected function toolSchemas(): array
    {
        return [
            [
                'name'        => 'getTrend',
                'description' => 'Cari postingan/tren yang relevan dengan sebuah kata kunci, lintas semua sumber/kompetitor yang dipantau.',
                'properties'  => [
                    'keyword' => ['type' => 'string', 'description' => "Kata kunci pencarian, misal 'diskon lebaran'"],
                    'limit'   => ['type' => 'integer', 'description' => 'Jumlah maksimal hasil, default 20'],
                ],
                'required' => [],
            ],
            [
                'name'        => 'getCompetitorPrice',
                'description' => 'Ambil detail satu kompetitor/target tertentu beserta postingan terbarunya.',
                'properties'  => [
                    'nama'  => ['type' => 'string', 'description' => "Nama kompetitor/target, misal 'Mixue'"],
                    'limit' => ['type' => 'integer', 'description' => 'Jumlah maksimal postingan, default 10'],
                ],
                'required' => ['nama'],
            ],
            [
                'name'        => 'getSummary',
                'description' => 'Ringkasan aktivitas & postingan terbaru lintas semua kompetitor/target dalam N hari terakhir.',
                'properties'  => [
                    'days' => ['type' => 'integer', 'description' => 'Rentang hari ke belakang, default 1'],
                ],
                'required' => [],
            ],
        ];
    }
}
