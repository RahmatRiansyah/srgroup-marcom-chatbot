<?php

namespace App\Services\Concerns;

/**
 * Logic yang dipakai bersama oleh ClaudeService, GroqService & GeminiService:
 * definisi tool getTrend/getCompetitorPrice/getSummary/getGoogleTrendsNow/
 * getEngagement/getMetaPosts, cara menjalankannya lewat AnalyticsApiService,
 * dan system prompt-nya. Disatukan di sini supaya jawaban chatbot konsisten
 * mau dijawab Claude, Groq, atau Gemini (fallback).
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
                (int) ($input['limit'] ?? 20),
                (int) ($input['days'] ?? 30)
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
            'getEngagement' => $this->analytics->getMetaEngagementSummary(
                (int) ($input['days'] ?? 7)
            ),
            'getMetaPosts' => $this->analytics->getMetaPosts(
                (int) ($input['limit'] ?? 10)
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

Kamu punya beberapa tools, dibagi 3 kategori:

DATA YANG SUDAH DIPANTAU TIM (dari kompetitor/keyword yang terdaftar di sistem, hasil
scraping -- caption/teks saja, TIDAK ada angka like/komentar resmi):
- getTrend: cari postingan/tren berdasarkan kata kunci di antara sumber yang dipantau
- getCompetitorPrice: detail & postingan terbaru satu kompetitor/target tertentu (butuh nama)
- getSummary: ringkasan aktivitas semua kompetitor/target dalam N hari terakhir

PERFORMA AKUN META SENDIRI (Instagram/Facebook resmi SR Group, data ASLI dari Meta Graph
API -- like, komentar, saved, shares, reach, engagement rate, disinkron berkala jadi
selalu relatif baru, BUKAN hasil scraping):
- getEngagement: ringkasan engagement akun sendiri dalam N hari terakhir (rata-rata
  engagement rate, post terbaik, kesegaran data lewat field "data_age_minutes")
- getMetaPosts: daftar post terbaru akun sendiri beserta angka engagement per post
- PENTING: tool ini HANYA untuk akun Meta milik SR Group sendiri. Kalau user tanya
  engagement/performa akun KOMPETITOR, jangan pakai tool ini -- jelaskan bahwa Meta Graph
  API cuma bisa diakses untuk akun yang dikelola sendiri, jadi untuk kompetitor pakai
  getCompetitorPrice/getTrend (cuma dapat konten/caption, bukan angka like/komentar resmi)

DATA LIVE (di luar kompetitor/keyword yang sudah terdaftar):
- getGoogleTrendsNow: query LANGSUNG ke Google Trends untuk satu kata kunci — pakai ini
  kalau user tanya soal keyword/topik yang belum tentu ada di data yang dipantau tim
- web_search (kalau tersedia): cari di web umum untuk berita/tren terkini yang di luar
  cakupan Google Trends maupun data internal, mis. "kompetitor baru apa yang muncul soal X"

Urutan yang disarankan: coba dulu getTrend/getCompetitorPrice/getSummary/getEngagement/
getMetaPosts (data internal paling terpercaya, sesuai kategori pertanyaannya). Kalau
hasilnya kosong atau pertanyaannya di luar cakupan data yang dipantau, baru pakai
getGoogleTrendsNow atau web_search untuk cari yang sedang tren saat ini secara umum. Kalau
semua tool tetap kosong atau error, katakan terus terang datanya belum tersedia — jangan
menebak-nebak. Kalau memakai data live (getGoogleTrendsNow/web_search), sebutkan ke user
bahwa itu data umum dari luar sistem, bukan hasil pemantauan khusus tim.

PENTING soal kesegaran data: getTrend & getCompetitorPrice mengembalikan field
"newest_post_age_days" (umur postingan terbaru yang ditemukan, dalam hari). Kamu WAJIB
memperhatikan angka ini:
- Kalau newest_post_age_days masih kecil (beberapa hari), boleh sampaikan datanya sebagai
  aktivitas terkini.
- Kalau angkanya cukup besar (mis. lebih dari 1-2 minggu) atau hasilnya kosong, JANGAN
  menyajikannya seolah itu tren yang sedang terjadi sekarang -- katakan terus terang bahwa
  data pemantauan tim untuk topik ini sudah agak lama / belum ada yang baru, lalu tawarkan
  cek getGoogleTrendsNow atau web_search untuk gambaran yang lebih update saat ini.
- Jangan pernah mengarang atau mengasumsikan data itu "baru" tanpa mengecek field ini
  dulu, karena tugas utamamu adalah analisis TREN yang harus selalu relate dengan kondisi
  sekarang, bukan histori lama.

getEngagement juga punya field kesegaran serupa, namanya "data_age_minutes" (dalam menit,
bukan hari -- karena sync-nya tiap 30 menit, jauh lebih sering dari scraping kompetitor
yang harian). Kalau angkanya besar (mis. berjam-jam/berhari-hari, tandanya scheduler sync
mungkin berhenti jalan di server), sebutkan itu ke user alih-alih diam-diam menyajikannya
seolah baru saja terjadi.

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
                'description' => 'Cari postingan/tren yang relevan dengan sebuah kata kunci, lintas semua sumber/kompetitor yang dipantau. Hasil SELALU dibatasi ke rentang hari terakhir (lihat "days") supaya datanya relevan dengan kondisi sekarang, bukan data basi.',
                'properties'  => [
                    'keyword' => ['type' => 'string', 'description' => "Kata kunci pencarian, misal 'diskon lebaran'"],
                    'limit'   => ['type' => 'integer', 'description' => 'Jumlah maksimal hasil, default 20'],
                    'days'    => ['type' => 'integer', 'description' => 'Batasi hasil ke N hari terakhir saja, default 30. Perbesar cuma kalau user memang minta data historis/lama.'],
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
            [
                'name'        => 'getEngagement',
                'description' => 'Ringkasan engagement akun Instagram/Facebook RESMI milik SR Group sendiri (BUKAN kompetitor) dalam N hari terakhir — rata-rata engagement rate, post dengan performa terbaik, dan total post. Data asli dari Meta Graph API (like, komentar, saved, shares, reach), disinkron berkala.',
                'properties'  => [
                    'days' => ['type' => 'integer', 'description' => 'Rentang hari ke belakang, default 7'],
                ],
                'required' => [],
            ],
            [
                'name'        => 'getMetaPosts',
                'description' => 'Daftar post terbaru akun Instagram/Facebook RESMI milik SR Group sendiri (BUKAN kompetitor) beserta angka engagement lengkap per post (like, komentar, saved, shares, reach, engagement rate).',
                'properties'  => [
                    'limit' => ['type' => 'integer', 'description' => 'Jumlah maksimal post, default 10'],
                ],
                'required' => [],
            ],
        ];
    }
}