<?php

namespace App\Services\Concerns;

/**
 * Logic yang dipakai bersama oleh ClaudeService, GroqService & GeminiService:
 * definisi tool getTrend/getCompetitorPrice/getSummary/getGoogleTrendsNow,
 * cara menjalankannya lewat AnalyticsApiService, dan system prompt-nya.
 * Disatukan di sini supaya jawaban chatbot konsisten mau dijawab Claude,
 * Groq, atau Gemini (fallback).
 *
 * Tool "web_search" bawaan Anthropic TIDAK didefinisikan di sini karena
 * formatnya spesifik Anthropic (server tool) — lihat ClaudeService::toolDefinitions().
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
            'getGoogleTrendsNow' => $this->analytics->getGoogleTrendsNow(
                $input['keyword'] ?? '',
                $input['geo'] ?? 'ID'
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
berdasarkan data yang benar-benar ada — jangan pernah mengarang data.

Kamu punya beberapa tools, dibagi 2 kategori:

DATA YANG SUDAH DIPANTAU TIM (dari kompetitor/keyword yang terdaftar di sistem):
- getTrend: cari postingan/tren berdasarkan kata kunci di antara sumber yang dipantau
- getCompetitorPrice: detail & postingan terbaru satu kompetitor/target tertentu (butuh nama)
- getSummary: ringkasan aktivitas semua kompetitor/target dalam N hari terakhir

DATA LIVE (di luar kompetitor/keyword yang sudah terdaftar):
- getGoogleTrendsNow: query LANGSUNG ke Google Trends untuk satu kata kunci — pakai ini
  kalau user tanya soal keyword/topik yang belum tentu ada di data yang dipantau tim
- web_search (kalau tersedia): cari di web umum untuk berita/tren terkini yang di luar
  cakupan Google Trends maupun data internal, mis. "kompetitor baru apa yang muncul soal X"

Urutan yang disarankan: coba dulu getTrend/getCompetitorPrice/getSummary (data internal
paling terpercaya untuk kompetitor yang memang dipantau tim). Kalau hasilnya kosong atau
pertanyaannya di luar cakupan data yang dipantau, baru pakai getGoogleTrendsNow atau
web_search untuk cari yang sedang tren saat ini secara umum. Kalau semua tool tetap kosong
atau error, katakan terus terang datanya belum tersedia — jangan menebak-nebak. Kalau
memakai data live (getGoogleTrendsNow/web_search), sebutkan ke user bahwa itu data umum
dari luar sistem, bukan hasil pemantauan khusus tim.

Untuk sapaan atau pertanyaan strategi umum yang tidak butuh data spesifik, jawab langsung
tanpa tools. Selalu jawab dalam Bahasa Indonesia: singkat, jelas, dan actionable untuk tim
marketing.
TEXT;
    }

    /** Skema 4 tools, format generik (dipakai ulang oleh masing-masing service dengan penyesuaian key). */
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
            [
                'name'        => 'getGoogleTrendsNow',
                'description' => 'Query LANGSUNG ke Google Trends (bukan data lama hasil scraping) untuk satu kata kunci — dipakai kalau kata kunci/topiknya belum tentu ada di kompetitor/target yang sudah terdaftar di sistem.',
                'properties'  => [
                    'keyword' => ['type' => 'string', 'description' => "Kata kunci yang mau dicek trennya, misal 'diskon lebaran'"],
                    'geo'     => ['type' => 'string', 'description' => "Kode negara 2 huruf, default 'ID' (Indonesia)"],
                ],
                'required' => ['keyword'],
            ],
        ];
    }
}