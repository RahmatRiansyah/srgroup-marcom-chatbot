<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunScrapeJob;
use App\Models\ScrapeLog;
use Illuminate\Support\Facades\DB;

class ScrapeLogController extends Controller
{
    /**
     * Tampilkan riwayat scraping (status scheduler) untuk tim marcom.
     */
    public function index()
    {
        $logs = ScrapeLog::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.scrapelog.index', compact('logs'));
    }

    /**
     * Jalankan scraping sekarang juga (tombol manual).
     *
     * PENTING: job didorong ke QUEUE (background), BUKAN dijalankan langsung
     * di proses request web ini. Sebelumnya pakai Artisan::call('scrape:run')
     * langsung di sini -- itu tetap kena batas max_execution_time PHP (60
     * detik), dan sejak scraper.py bisa memakai headless browser untuk target
     * Instagram/TikTok, satu run scraping bisa lebih lama dari itu -> Fatal
     * Error "Maximum execution time exceeded". Lihat App\Jobs\RunScrapeJob
     * untuk detail & catatan penting soal queue worker.
     */
    public function runNow()
    {
        // Guard sederhana: cegah klik dobel bikin 2 proses scraping jalan
        // bersamaan (numpuk beban ke mesin Python & headless browser,
        // berpotensi bikin angka di scrape_logs kacau kalau race condition).
        $alreadyQueued = DB::table('jobs')
            ->where('payload', 'like', '%RunScrapeJob%')
            ->exists();

        if ($alreadyQueued) {
            return redirect()
                ->route('admin.scrapelog.index')
                ->with('warning', 'Ada proses scraping yang masih berjalan/menunggu di background. Tunggu sampai selesai sebelum menjalankan lagi.');
        }

        RunScrapeJob::dispatch();

        return redirect()
            ->route('admin.scrapelog.index')
            ->with('success', 'Scraping sudah dijadwalkan & sedang berjalan di background. Refresh halaman ini beberapa saat lagi untuk melihat hasilnya.');
    }
}