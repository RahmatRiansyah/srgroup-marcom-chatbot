<?php

namespace App\Console\Commands;

use App\Models\ScrapeLog;
use App\Services\AnalyticsApiService;
use Illuminate\Console\Command;

class RunScrape extends Command
{
    /**
     * Nama & signature command console.
     *
     * Dipanggil otomatis tiap hari lewat scheduler (lihat routes/console.php),
     * dan bisa dipanggil manual: php artisan scrape:run
     */
    protected $signature = 'scrape:run';

    protected $description = 'Jalankan scraping data tren/kompetitor lewat mesin analisis Python, lalu catat hasilnya ke scrape_logs.';

    public function handle(AnalyticsApiService $analytics): int
    {
        $this->info('Memulai scraping lewat mesin analisis Python...');

        $result = $analytics->triggerScrape();

        // AnalyticsApiService gagal total menghubungi service Python (down/timeout/dsb)
        if ($result['error'] ?? false) {
            ScrapeLog::create([
                'status'  => 'failed',
                'message' => $result['message'] ?? 'Gagal menjalankan scraping.',
            ]);

            $this->error($result['message'] ?? 'Gagal menjalankan scraping.');

            return self::FAILURE;
        }

        $total   = (int) ($result['total'] ?? 0);
        $success = (int) ($result['success'] ?? 0);
        $failed  = (int) ($result['failed'] ?? 0);

        $status = match (true) {
            $total === 0 => 'failed',
            $failed === 0 => 'success',
            $success === 0 => 'failed',
            default => 'partial',
        };

        ScrapeLog::create([
            'status'        => $status,
            'total_targets' => $total,
            'success_count' => $success,
            'failed_count'  => $failed,
            'message'       => $total === 0 ? 'Tidak ada target aktif dengan URL yang valid.' : null,
            'details'       => $result['results'] ?? [],
        ]);

        $this->info("Selesai. {$success}/{$total} target berhasil, {$failed} gagal.");

        return self::SUCCESS;
    }
}
