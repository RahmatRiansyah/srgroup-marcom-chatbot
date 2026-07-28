<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrendSource;
use App\Models\TrendPost;
use App\Models\ChatSession;
use App\Models\MetaAccountSnapshot;
use App\Models\MetaPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        return view('dashboard', compact(
            'totalSources',
            'totalPosts',
            'totalChats',
            'recentPosts',
            'platformDistribution',
            'metaSnapshot',
            'metaAvgEngagementRate',
            'metaBestPost',
            'metaLastSyncedAt'
        ));
    }
}