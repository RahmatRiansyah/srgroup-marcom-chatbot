<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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
     * Cek cepat apakah mesin analisis (FastAPI) hidup, lewat GET / (health_check
     * di app/main.py, tidak butuh API key). Hasilnya di-cache 30 detik supaya
     * tiap panggilan get()/triggerScrape()/triggerMetaSync() tidak ikut nunggu
     * timeout penuh satu-satu kalau service-nya memang lagi down -- cukup satu
     * probe ringan (timeout 3 detik) yang dipakai bersama selama jendela 30
     * detik itu. Setelah 30 detik, dicoba lagi otomatis (self-healing check).
     */
    public function isHealthy(): bool
    {
        return (bool) Cache::remember('analytics_service_health', 30, function () {
            try {
                return Http::timeout(3)->get($this->baseUrl . '/')->successful();
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    /**
     * Guard dipanggil di awal tiap method publik yang hit FastAPI. Return array
     * error langsung (tanpa nunggu timeout $this->timeout / 120s / 150s) kalau
     * service diketahui down, atau null kalau boleh lanjut request asli.
     */
    protected function serviceDownResponse(): ?array
    {
        if ($this->isHealthy()) {
            return null;
        }

        Log::warning('AnalyticsApiService: skip request, service analytics down (health check gagal)');

        return [
            'error'        => true,
            'service_down' => true,
            'message'      => 'Layanan analytics (Python) sedang tidak bisa diakses. Coba lagi beberapa saat lagi.',
        ];
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
        if ($down = $this->serviceDownResponse()) {
            return $down;
        }

        try {
            // FIX: sebelumnya 120 detik, padahal RunScrapeJob (pemanggil method
            // ini lewat ScrapeRunnerService) dikasih budget $timeout = 600 detik
            // karena headless browser buat Instagram/TikTok bisa lama -- pola
            // sama seperti yang sudah diperbaiki di triggerMetaSync(). Sekarang
            // scraper.py juga punya retry terbatas untuk kegagalan sementara
            // (network hiccup), yang menambah durasi lagi -- 570 detik (buffer
            // 30 detik dari budget job) supaya retry itu nggak keburu kepotong
            // di sini duluan sebelum sempat selesai wajar.
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->timeout(570)
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
     * POST /meta/sync
     * Tarik data TERBARU dari Meta Graph API (akun Instagram Business/Creator
     * MILIK SR GROUP SENDIRI -- beda dari triggerScrape() yang untuk
     * kompetitor) dan simpan/update ke meta_posts & meta_account_snapshots.
     * Dipanggil scheduler (php artisan meta:sync) & tombol "Sync Sekarang".
     */
    public function triggerMetaSync(): array
    {
        if ($down = $this->serviceDownResponse()) {
            return $down;
        }

        try {
            // FIX: sebelumnya timeout di sini cuma 60 detik, padahal
            // RunMetaSyncJob (yang manggil method ini lewat
            // MetaSyncRunnerService) sengaja dikasih budget $timeout = 180
            // detik karena sync Meta hit Graph API SATU PER SATU per post
            // (profil + daftar media + insight tiap post, bisa 25 post).
            // Kalau Graph API lagi lambat/di-throttle & totalnya jatuh di
            // rentang 60-180 detik, request ini akan timeout duluan &
            // dilaporkan "Gagal" padahal job-nya sendiri masih ada waktu.
            // Disamakan ke 150 detik (sisa 30 detik buffer dari budget job)
            // supaya konsisten dengan job timeout-nya.
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->timeout(150)
                ->post($this->baseUrl . '/meta/sync');

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('AnalyticsApiService: trigger meta sync gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'error'   => true,
                'status'  => $response->status(),
                // Body dari FastAPI biasanya berisi {"detail": "..."} yang isinya
                // pesan Graph API asli (mis. token expired) -- lebih berguna
                // ditampilkan ke admin daripada pesan generik.
                'message' => $response->json('detail') ?? 'Mesin analisis mengembalikan error saat sync Meta.',
            ];
        } catch (\Exception $e) {
            Log::error('AnalyticsApiService: exception saat trigger meta sync', [
                'message' => $e->getMessage(),
            ]);

            return [
                'error'   => true,
                'message' => 'Tidak bisa menghubungi mesin analisis: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * GET /meta/engagement/summary?days=...
     * Ringkasan engagement akun Meta sendiri: rata-rata engagement rate,
     * post terbaik, dan kesegaran data (dibaca dari DB lokal hasil sync
     * terakhir, bukan hit Graph API langsung).
     */
    public function getMetaEngagementSummary(int $days = 7): array
    {
        return $this->get('/meta/engagement/summary', ['days' => $days]);
    }

    /**
     * GET /meta/posts?limit=...
     * Daftar post terbaru akun Meta sendiri beserta angka engagement-nya.
     */
    public function getMetaPosts(int $limit = 10): array
    {
        return $this->get('/meta/posts', ['limit' => $limit]);
    }

    /**
     * Helper GET generik dengan error handling yang konsisten.
     */
    protected function get(string $path, array $query = []): array
    {
        if ($down = $this->serviceDownResponse()) {
            return $down;
        }

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