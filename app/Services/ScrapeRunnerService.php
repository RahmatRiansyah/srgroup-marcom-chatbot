<?php

namespace App\Services;

use App\Models\ScrapeLog;
use App\Models\User;
use App\Notifications\ScrapeFailedRepeatedly;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/**
 * Logika inti "jalankan scraping lewat mesin Python, lalu catat hasilnya ke
 * scrape_logs" -- diekstrak ke sini supaya bisa dipakai ULANG dari 2 tempat
 * tanpa duplikasi:
 *
 *  1. app/Console/Commands/RunScrape.php  -> dipanggil scheduler harian (06:00)
 *     & bisa juga manual lewat `php artisan scrape:run` di terminal.
 *  2. app/Jobs/RunScrapeJob.php            -> dipanggil dari tombol "Jalankan
 *     Sekarang" di admin, lewat QUEUE (background), BUKAN langsung di proses
 *     request web -- supaya klik tombol tidak perlu nunggu scraping selesai
 *     (bisa 10-an detik sampai beberapa menit sejak ada headless browser
 *     untuk Instagram/TikTok) & tidak lagi kena Fatal Error
 *     "Maximum execution time exceeded".
 */
class ScrapeRunnerService
{
    public function __construct(protected AnalyticsApiService $analytics)
    {
    }

    public function run(): ScrapeLog
    {
        $result = $this->analytics->triggerScrape();

        // AnalyticsApiService gagal total menghubungi service Python (down/timeout/dsb)
        if ($result['error'] ?? false) {
            return $this->saveLog([
                'status'  => 'failed',
                'message' => $result['message'] ?? 'Gagal menjalankan scraping.',
            ]);
        }

        $total     = (int) ($result['total'] ?? 0);
        $success   = (int) ($result['success'] ?? 0);
        $unchanged = (int) ($result['unchanged'] ?? 0);
        $failed    = (int) ($result['failed'] ?? 0);

        // Status murni berdasar ADA/TIDAKNYA kegagalan nyata:
        // - tidak ada target sama sekali -> 'failed' (tidak ada yang bisa diproses)
        // - SEMUA target gagal -> 'failed'
        // - SEBAGIAN gagal (sisanya sukses/tidak berubah) -> 'partial'
        // - TIDAK ADA yang gagal (baik baru maupun tanpa perubahan) -> 'success'
        $status = match (true) {
            $total === 0 => 'failed',
            $failed === $total => 'failed',
            $failed > 0 => 'partial',
            default => 'success',
        };

        return $this->saveLog([
            'status'          => $status,
            'total_targets'   => $total,
            'success_count'   => $success,
            'unchanged_count' => $unchanged,
            'failed_count'    => $failed,
            'message'         => $total === 0 ? 'Tidak ada target aktif dengan URL yang valid.' : null,
            'details'         => $result['results'] ?? [],
        ]);
    }

    protected function saveLog(array $attributes): ScrapeLog
    {
        $log = ScrapeLog::create($attributes);

        $this->maybeNotifyConsecutiveFailures($log);

        return $log;
    }

    /**
     * Kirim notifikasi (email + Telegram kalau dikonfigurasi) ke semua admin
     * kalau scraping sudah GAGAL TOTAL sejumlah services.scrape_alert
     * .failure_threshold kali BERTURUT-TURUT (default 3).
     *
     * Sengaja dibatasi maksimal 1 notifikasi / 12 jam (lewat Cache lock) --
     * supaya kalau service Python masih down berhari-hari & scheduler/tombol
     * "Jalankan Sekarang" terus dicoba, admin tidak dibanjiri email/Telegram
     * berulang-ulang untuk masalah yang sama.
     */
    protected function maybeNotifyConsecutiveFailures(ScrapeLog $log): void
    {
        $lockKey = 'scrape_failure_alert_sent';

        if ($log->status !== 'failed') {
            // Rentetan kegagalan terputus -- reset supaya lain kali gagal lagi
            // sampai threshold, notifikasi bisa terkirim lagi (bukan ke-skip
            // terus gara-gara lock lama).
            Cache::forget($lockKey);

            return;
        }

        $threshold = (int) config('services.scrape_alert.failure_threshold', 3);

        $recentStatuses = ScrapeLog::latest('id')->limit($threshold)->pluck('status');

        $isConsecutiveFailure = $recentStatuses->count() >= $threshold
            && $recentStatuses->every(fn ($status) => $status === 'failed');

        if (!$isConsecutiveFailure || Cache::has($lockKey)) {
            return;
        }

        Cache::put($lockKey, true, now()->addHours(12));

        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new ScrapeFailedRepeatedly($log, $threshold));
        }
    }
}
