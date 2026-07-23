<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrendSource;
use App\Models\TrendPost;
use App\Models\ChatSession;
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

        return view('dashboard', compact(
            'totalSources',
            'totalPosts',
            'totalChats',
            'recentPosts',
            'platformDistribution'
        ));
    }
}