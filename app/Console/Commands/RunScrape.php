<?php

namespace App\Console\Commands;

use App\Services\ScrapeRunnerService;
use Illuminate\Console\Command;

class RunScrape extends Command
{
    /**
     * Nama & signature command console.
     *
     * Dipanggil otomatis tiap hari lewat scheduler (lihat routes/console.php),
     * dan bisa dipanggil manual: php artisan scrape:run
     *
     * Logika intinya ada di ScrapeRunnerService supaya bisa dipakai ulang oleh
     * RunScrapeJob (dipanggil dari tombol "Jalankan Sekarang" di admin lewat
     * queue/background, lihat app/Jobs/RunScrapeJob.php).
     */
    protected $signature = 'scrape:run';

    protected $description = 'Jalankan scraping data tren/kompetitor lewat mesin analisis Python, lalu catat hasilnya ke scrape_logs.';

    public function handle(ScrapeRunnerService $runner): int
    {
        $this->info('Memulai scraping lewat mesin analisis Python...');

        $log = $runner->run();

        if ($log->status === 'failed' && $log->total_targets === 0) {
            $this->error($log->message ?? 'Gagal menjalankan scraping.');

            return self::FAILURE;
        }

        $this->info("Selesai. {$log->success_count} baru, {$log->unchanged_count} tanpa perubahan, {$log->failed_count} gagal (dari {$log->total_targets} target).");

        return self::SUCCESS;
    }
}
