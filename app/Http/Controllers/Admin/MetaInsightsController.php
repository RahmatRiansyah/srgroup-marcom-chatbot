<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunMetaSyncJob;
use App\Models\MetaAccountSnapshot;
use App\Models\MetaPost;
use App\Models\MetaSyncLog;
use Illuminate\Support\Facades\DB;

class MetaInsightsController extends Controller
{
    /**
     * Halaman "Performa Meta": engagement rate, post terbaik, tabel post
     * terbaru, & riwayat sync -- semua dibaca LANGSUNG dari DB lokal lewat
     * Eloquent (bukan lewat AnalyticsApiService/HTTP), pola sama seperti
     * DashboardController & DataSourceController membaca TrendSource/
     * TrendPost langsung. AnalyticsApiService dipakai khusus untuk hit
     * dari sisi chatbot & untuk memicu sync itu sendiri.
     */
    public function index()
    {
        $latestSnapshot = MetaAccountSnapshot::orderBy('snapshot_at', 'desc')->first();

        $posts = MetaPost::orderBy('posted_at', 'desc')->paginate(15);

        $bestPost = MetaPost::orderByRaw('COALESCE(engagement_rate_reach, engagement_rate_followers, 0) DESC')
            ->first();

        $avgEngagementRate = MetaPost::query()
            ->whereNotNull('engagement_rate_reach')
            ->avg('engagement_rate_reach');

        $lastSyncedAt = MetaPost::max('fetched_at');

        $syncLogs = MetaSyncLog::orderBy('created_at', 'desc')->take(10)->get();

        return view('admin.meta-insights.index', compact(
            'latestSnapshot',
            'posts',
            'bestPost',
            'avgEngagementRate',
            'lastSyncedAt',
            'syncLogs'
        ));
    }

    /**
     * Trigger sync manual ke Meta Graph API (tombol "Sync Sekarang"),
     * pola identik dengan ScrapeLogController::runNow() -- didorong ke
     * queue (background) supaya tidak kena batas max_execution_time PHP.
     */
    public function syncNow()
    {
        $alreadyQueued = DB::table('jobs')
            ->where('payload', 'like', '%RunMetaSyncJob%')
            ->exists();

        if ($alreadyQueued) {
            return redirect()
                ->route('admin.meta-insights.index')
                ->with('warning', 'Ada proses sync Meta yang masih berjalan/menunggu di background. Tunggu sampai selesai sebelum menjalankan lagi.');
        }

        RunMetaSyncJob::dispatch();

        return redirect()
            ->route('admin.meta-insights.index')
            ->with('success', 'Sync ke Meta Graph API sudah dijadwalkan & sedang berjalan di background. Refresh halaman ini beberapa saat lagi.');
    }
}