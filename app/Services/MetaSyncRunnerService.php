<?php

namespace App\Services;

use App\Models\MetaSyncLog;

/**
 * Logika inti "sync ke Meta Graph API lewat mesin Python, lalu catat
 * hasilnya ke meta_sync_logs" -- dipakai ulang dari 2 tempat, sama seperti
 * ScrapeRunnerService:
 *
 *  1. app/Console/Commands/RunMetaSync.php -> dipanggil scheduler tiap
 *     beberapa menit (lihat routes/console.php) & manual lewat
 *     `php artisan meta:sync`.
 *  2. app/Jobs/RunMetaSyncJob.php -> dipanggil dari tombol "Sync Sekarang"
 *     di admin, lewat QUEUE (background) supaya request web tidak perlu
 *     menunggu beberapa detik proses hit Graph API + insert per post.
 */
class MetaSyncRunnerService
{
    public function __construct(protected AnalyticsApiService $analytics)
    {
    }

    public function run(): MetaSyncLog
    {
        $result = $this->analytics->triggerMetaSync();

        if ($result['error'] ?? false) {
            return MetaSyncLog::create([
                'status'  => 'failed',
                'message' => $result['message'] ?? 'Gagal menjalankan sync Meta.',
            ]);
        }

        $synced = (int) ($result['posts_synced'] ?? 0);
        $failed = (int) ($result['posts_failed'] ?? 0);

        $status = match (true) {
            $synced === 0 && $failed === 0 => 'failed',
            $failed > 0 && $synced > 0 => 'partial',
            $failed > 0 && $synced === 0 => 'failed',
            default => 'success',
        };

        return MetaSyncLog::create([
            'status'       => $status,
            'posts_synced' => $synced,
            'posts_failed' => $failed,
            'message'      => $synced === 0 && $failed === 0 ? 'Tidak ada post yang ditemukan di akun.' : null,
            'details'      => $result,
        ]);
    }
}