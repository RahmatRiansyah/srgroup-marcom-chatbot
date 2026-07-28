<?php

namespace App\Console\Commands;

use App\Services\MetaSyncRunnerService;
use Illuminate\Console\Command;

class RunMetaSync extends Command
{
    /**
     * Dipanggil otomatis tiap beberapa menit lewat scheduler (lihat
     * routes/console.php) supaya data engagement Meta di dashboard/chatbot
     * selalu segar ("real-time" dalam arti polling berkala -- Meta sendiri
     * tidak menyediakan push live untuk perubahan like/komentar/reach).
     * Bisa juga dipanggil manual: php artisan meta:sync
     */
    protected $signature = 'meta:sync';

    protected $description = 'Tarik data engagement terbaru dari Meta Graph API lewat mesin analisis Python, lalu catat hasilnya ke meta_sync_logs.';

    public function handle(MetaSyncRunnerService $runner): int
    {
        $this->info('Memulai sync ke Meta Graph API...');

        $log = $runner->run();

        if ($log->status === 'failed') {
            $this->error($log->message ?? 'Gagal menjalankan sync Meta.');

            return self::FAILURE;
        }

        $this->info("Selesai. {$log->posts_synced} post ter-sync, {$log->posts_failed} gagal.");

        return self::SUCCESS;
    }
}