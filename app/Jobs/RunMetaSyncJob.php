<?php

namespace App\Jobs;

use App\Services\MetaSyncRunnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Jalankan sync Meta di BACKGROUND lewat queue worker, dipanggil dari
 * tombol "Sync Sekarang" di Admin\MetaInsightsController::syncNow() --
 * pola sama persis dengan RunScrapeJob, dengan alasan yang sama: supaya
 * klik tombol tidak kena batas max_execution_time PHP.
 *
 * WAJIB ada queue worker aktif (php artisan queue:work) supaya job ini
 * benar-benar dieksekusi -- lihat catatan lengkap di RunScrapeJob.php.
 */
class RunMetaSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180; // 3 menit -- cukup untuk hit Graph API per post secara berurutan

    public int $tries = 2;

    public function handle(MetaSyncRunnerService $runner): void
    {
        Log::info('RunMetaSyncJob: mulai sync Meta di background (dipicu manual dari admin).');

        $log = $runner->run();

        Log::info('RunMetaSyncJob: selesai.', [
            'status'       => $log->status,
            'posts_synced' => $log->posts_synced,
            'posts_failed' => $log->posts_failed,
        ]);
    }
}