<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScrapeLog;
use Illuminate\Support\Facades\Artisan;

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
     * Jalankan scraping sekarang juga (tombol manual), lewat command yang
     * sama dipakai scheduler harian, supaya perilaku & logging-nya konsisten.
     */
    public function runNow()
    {
        Artisan::call('scrape:run');

        return redirect()
            ->route('admin.scrapelog.index')
            ->with('success', 'Scraping selesai dijalankan. Lihat hasilnya di tabel di bawah.');
    }
}
