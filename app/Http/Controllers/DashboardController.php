<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrendSource;
use App\Models\TrendPost;
use App\Models\ChatSession;
use App\Models\MetaAccountSnapshot;
use App\Models\MetaPost;
use App\Models\MetaSyncLog;
use App\Models\ScrapeLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Data Ringkas (Stat Cards)
        $totalSources = TrendSource::count();
        $totalPosts = TrendPost::count();
        $totalChats = ChatSession::where('user_id', $userId)->count();

        // 2. 5 Postingan Tren Terbaru
        $recentPosts = TrendPost::with('trendSource')->latest()->take(5)->get();

        // 3. Data Distribusi Platform untuk Chart (misal: Instagram, TikTok, Web)
        $platformDistribution = TrendSource::select('platform', DB::raw('count(*) as total'))
            ->groupBy('platform')
            ->pluck('total', 'platform');

        // 4. Ringkasan Performa Meta (akun IG/FB SR Group sendiri, hasil sync
        //    berkala dari Graph API -- lihat MetaInsightsController untuk
        //    tampilan detailnya). Dibuat null-safe karena kredensial Meta
        //    bisa saja belum di-setup tim, jadi tabel ini masih kosong.
        $metaSnapshot = MetaAccountSnapshot::orderBy('snapshot_at', 'desc')->first();
        $metaAvgEngagementRate = MetaPost::whereNotNull('engagement_rate_reach')->avg('engagement_rate_reach');
        $metaBestPost = MetaPost::orderByRaw('COALESCE(engagement_rate_reach, engagement_rate_followers, 0) DESC')->first();
        $metaLastSyncedAt = MetaPost::max('fetched_at');

        // 5. Status Operasional -- log terbaru dari scraping kompetitor
        //    (App\Jobs\RunScrapeJob) dan sync Meta (App\Console\Commands
        //    yang menulis ke meta_sync_logs), biar kelihatan di dashboard
        //    tanpa buka halaman admin masing-masing dulu.
        $latestScrapeLog = ScrapeLog::orderBy('created_at', 'desc')->first();
        $latestMetaSyncLog = MetaSyncLog::orderBy('created_at', 'desc')->first();

        // 6. Tren Aktivitas Kompetitor -- jumlah TrendPost baru per hari,
        //    14 hari terakhir. Hari tanpa data tetap ditampilkan dengan
        //    nilai 0 supaya sumbu chart tidak bolong.
        $rawTrendCounts = TrendPost::where('created_at', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $trendLabels = [];
        $trendCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->format('Y-m-d');
            $trendLabels[] = $date->translatedFormat('d M');
            $trendCounts[] = (int) ($rawTrendCounts[$key] ?? 0);
        }

        return view('dashboard', compact(
            'totalSources',
            'totalPosts',
            'totalChats',
            'recentPosts',
            'platformDistribution',
            'metaSnapshot',
            'metaAvgEngagementRate',
            'metaBestPost',
            'metaLastSyncedAt',
            'latestScrapeLog',
            'latestMetaSyncLog',
            'trendLabels',
            'trendCounts'
        ));
    }
}