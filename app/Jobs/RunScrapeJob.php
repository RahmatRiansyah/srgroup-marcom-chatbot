<?php

namespace App\Jobs;

use App\Services\ScrapeRunnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Menjalankan scraping (lewat ScrapeRunnerService) di BACKGROUND lewat queue
 * worker, dipanggil dari tombol "Jalankan Sekarang" di
 * Admin\ScrapeLogController::runNow().
 *
 * Kenapa perlu ini: sebelumnya tombol itu memanggil Artisan::call('scrape:run')
 * LANGSUNG di dalam proses request web, jadi ikut kena batas max_execution_time
 * PHP (default 60 detik di banyak setup lokal). Sejak scraper.py bisa memakai
 * headless browser (Playwright) untuk target Instagram/TikTok, satu run
 * scraping bisa makan waktu lebih dari itu -> Fatal Error "Maximum execution
 * time exceeded". Dengan didorong ke queue, request web cuma perlu <1 detik
 * untuk MENJADWALKAN job ini, prosesnya sendiri jalan terpisah di queue worker.
 *
 * ============================================================================
 * WAJIB: supaya job ini BENAR-BENAR jalan, harus ada queue worker aktif:
 *
 *     php artisan queue:work
 *
 * Kalau tidak ada worker yang jalan, job ini cuma akan MENUMPUK di tabel
 * `jobs` dan tidak pernah dieksekusi -- tombol "Jalankan Sekarang" akan
 * terlihat "berhasil" (redirect + pesan sukses) tapi datanya tidak pernah
 * ter-update. Untuk production, jalankan queue:work sebagai proses persisten
 * (mis. lewat Supervisor di VPS, atau worker service di Railway/Render).
 * ============================================================================
 */
class RunScrapeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Headless browser untuk beberapa target Instagram/TikTok sekaligus bisa
     * makan waktu total beberapa menit -- beri buffer jauh lebih besar dari
     * timeout HTTP ke mesin Python (120 detik, lihat AnalyticsApiService)
     * supaya worker tidak membunuh job ini duluan sebelum benar-benar selesai.
     */
    public int $timeout = 600; // 10 menit

    /**
     * Kalau job gagal karena exception tak terduga, cukup 1x diulang otomatis
     * -- supaya tidak diam-diam mengulang scraping berkali-kali kalau memang
     * ada masalah struktural (bukan sekadar hiccup jaringan sesaat).
     */
    public int $tries = 2;

    public function handle(ScrapeRunnerService $runner): void
    {
        Log::info('RunScrapeJob: mulai scraping di background (dipicu manual dari admin).');

        $log = $runner->run();

        Log::info('RunScrapeJob: selesai.', [
            'status'          => $log->status,
            'total_targets'   => $log->total_targets,
            'success_count'   => $log->success_count,
            'unchanged_count' => $log->unchanged_count,
            'failed_count'    => $log->failed_count,
        ]);
    }
}
