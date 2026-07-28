<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SR GROUP - Dashboard Analytics</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-white.svg') }}">
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CDN Chart.js untuk Visualisasi Grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#fbf9f8] text-[#1b1c1c] min-h-screen flex font-sans overflow-x-hidden">

    <!-- 1. Sidebar Navigasi -->
    @include('components.sidebar')

    <!-- 2. Area Utama Dashboard -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">
        
        <!-- Header -->
        <header class="bg-[#ffffff] border-b border-[#e5e5e1] py-4 px-6 flex justify-between items-center shadow-sm shadow-[#0000000d] shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden shrink-0 text-[#1b1c1c] hover:text-[#1b1c1c] bg-[#f5f3f3] hover:bg-[#efeded] p-2 rounded-lg transition" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="font-bold text-xl text-[#1b1c1c]">Marcom Analytics Dashboard</h1>
                    <p class="text-xs text-[#885215]">Ringkasan Performa Target, Kompetitor & Sesi AI</p>
                </div>
            </div>
            <div class="text-xs text-[#885215] bg-[#f5f3f3] px-3 py-1.5 rounded-lg border border-[#e5e5e1]">
                Terakhir Diperbarui: <span class="text-[#885215] font-semibold">{{ date('d M Y') }}</span>
            </div>
        </header>

        <!-- Main Content Grid -->
        <main class="p-6 space-y-6 max-w-7xl mx-auto w-full">

            <!-- ROW 1: Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- Card 1: Total Kompetitor -->
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 flex items-center justify-between shadow-sm shadow-[#0000000d] hover:border-[#885215]/40 transition">
                    <div>
                        <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Target / Kompetitor</p>
                        <h3 class="text-3xl font-extrabold text-[#1b1c1c] mt-1">{{ $totalSources }}</h3>
                        <p class="text-xs text-[#5f5e5e] mt-2 font-medium">✓ Terdaftar di sistem</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>

                <!-- Card 2: Total Postingan Tren -->
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 flex items-center justify-between shadow-sm shadow-[#0000000d] hover:border-[#885215]/40 transition">
                    <div>
                        <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Konten & Tren Disimpan</p>
                        <h3 class="text-3xl font-extrabold text-[#1b1c1c] mt-1">{{ $totalPosts }}</h3>
                        <p class="text-xs text-[#5f5e5e] mt-2 font-medium">Data rujukan AI</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                </div>

                <!-- Card 3: Total Sesi Chat AI -->
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 flex items-center justify-between shadow-sm shadow-[#0000000d] hover:border-[#885215]/40 transition">
                    <div>
                        <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Percakapan AI</p>
                        <h3 class="text-3xl font-extrabold text-[#1b1c1c] mt-1">{{ $totalChats }}</h3>
                        <p class="text-xs text-[#5f5e5e] mt-2 font-medium">Sesi analisis aktif</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                </div>

            </div>

            <!-- ROW 1B: Status Operasional -- scraping kompetitor & sync Meta,
                 biar langsung kelihatan "semua normal atau ada yang perlu
                 dicek" tanpa buka halaman Log Scraping/Meta admin dulu. -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Status Scraping Kompetitor -->
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Scraping Kompetitor</p>
                            <p class="text-xs text-[#5f5e5e] mt-1">
                                {{ $latestScrapeLog ? $latestScrapeLog->created_at->diffForHumans() : 'Belum pernah dijalankan' }}
                            </p>
                        </div>
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <a href="{{ route('admin.scrapelog.index') }}" class="text-xs text-[#885215] hover:underline shrink-0">Lihat Log →</a>
                        @endif
                    </div>

                    @if($latestScrapeLog)
                        @php
                            $scrapeBadge = match ($latestScrapeLog->status) {
                                'success' => ['bg-[#e8f3ea] text-[#1f7a3c] border-[#1f7a3c]/20', 'Normal'],
                                'partial' => ['bg-[#fdf3e3] text-[#8a5a00] border-[#8a5a00]/20', 'Sebagian Gagal'],
                                default => ['bg-[#fce9e9] text-[#b3261e] border-[#b3261e]/20', 'Gagal'],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $scrapeBadge[0] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $scrapeBadge[1] }}
                        </span>
                        <p class="text-xs text-[#847467] mt-2">
                            {{ $latestScrapeLog->success_count }} berhasil · {{ $latestScrapeLog->unchanged_count }} tidak berubah · {{ $latestScrapeLog->failed_count }} gagal
                        </p>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-[#f5f3f3] text-[#5f5e5e] border-[#e5e5e1]">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Belum Ada Data
                        </span>
                    @endif
                </div>

                <!-- Status Sync Meta -->
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Sync Performa Meta</p>
                            <p class="text-xs text-[#5f5e5e] mt-1">
                                {{ $latestMetaSyncLog ? $latestMetaSyncLog->created_at->diffForHumans() : 'Belum pernah dijalankan' }}
                            </p>
                        </div>
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <a href="{{ route('admin.meta-insights.index') }}" class="text-xs text-[#885215] hover:underline shrink-0">Lihat Detail →</a>
                        @endif
                    </div>

                    @if($latestMetaSyncLog)
                        @php
                            $metaSyncBadge = match ($latestMetaSyncLog->status) {
                                'success' => ['bg-[#e8f3ea] text-[#1f7a3c] border-[#1f7a3c]/20', 'Normal'],
                                'partial' => ['bg-[#fdf3e3] text-[#8a5a00] border-[#8a5a00]/20', 'Sebagian Gagal'],
                                default => ['bg-[#fce9e9] text-[#b3261e] border-[#b3261e]/20', 'Gagal'],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $metaSyncBadge[0] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $metaSyncBadge[1] }}
                        </span>
                        <p class="text-xs text-[#847467] mt-2">
                            {{ $latestMetaSyncLog->posts_synced }} post disinkron · {{ $latestMetaSyncLog->posts_failed }} gagal
                        </p>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-[#f5f3f3] text-[#5f5e5e] border-[#e5e5e1]">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Belum Ada Data
                        </span>
                    @endif
                </div>

            </div>

            <!-- ROW 1C: Performa Meta (akun IG/FB SR Group sendiri, real-time via Graph API) -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="font-bold text-base text-[#1b1c1c]">Performa Meta (Akun Sendiri)</h2>
                        <p class="text-xs text-[#5f5e5e]">
                            @if($metaLastSyncedAt)
                                Disinkron {{ \Illuminate\Support\Carbon::parse($metaLastSyncedAt)->diffForHumans() }} dari Meta Graph API
                            @else
                                Belum ada data -- setup kredensial Meta lalu jalankan sync pertama
                            @endif
                        </p>
                    </div>
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.meta-insights.index') }}" class="text-xs text-[#885215] hover:underline">Lihat Detail →</a>
                    @endif
                </div>

                @if($metaSnapshot)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-[#fbf9f8] border border-[#e5e5e1] rounded-xl p-4">
                            <p class="text-[10px] font-medium text-[#524439] uppercase tracking-wider">Followers</p>
                            <p class="text-xl font-bold text-[#1b1c1c] mt-1">{{ number_format($metaSnapshot->followers_count ?? 0) }}</p>
                            <p class="text-[11px] text-[#847467] mt-1">@{{ $metaSnapshot->username }}</p>
                        </div>
                        <div class="bg-[#fbf9f8] border border-[#e5e5e1] rounded-xl p-4">
                            <p class="text-[10px] font-medium text-[#524439] uppercase tracking-wider">Rata-rata Engagement Rate</p>
                            <p class="text-xl font-bold text-[#1b1c1c] mt-1">{{ $metaAvgEngagementRate ? number_format($metaAvgEngagementRate, 2) . '%' : '—' }}</p>
                            <p class="text-[11px] text-[#847467] mt-1">interaksi / reach per post</p>
                        </div>
                        <div class="bg-[#fbf9f8] border border-[#e5e5e1] rounded-xl p-4">
                            <p class="text-[10px] font-medium text-[#524439] uppercase tracking-wider">Post Terbaik</p>
                            <p class="text-xl font-bold text-[#1b1c1c] mt-1">
                                {{ $metaBestPost?->engagement_rate_reach ? number_format($metaBestPost->engagement_rate_reach, 2) . '%' : '—' }}
                            </p>
                            <p class="text-[11px] text-[#847467] mt-1 truncate">{{ $metaBestPost?->caption ? \Illuminate\Support\Str::limit($metaBestPost->caption, 28) : '—' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-[#847467] py-4 text-center">
                        Belum ada data engagement Meta.
                        @if(Auth::check() && Auth::user()->isAdmin())
                            Setup <code class="bg-[#f5f3f3] px-1.5 py-0.5 rounded">META_ACCESS_TOKEN</code> di admin panel lalu klik "Sync Sekarang" di menu <a href="{{ route('admin.meta-insights.index') }}" class="text-[#885215] hover:underline">Performa Meta</a>.
                        @endif
                    </p>
                @endif
            </div>

            <!-- ROW 2: Tren Aktivitas Kompetitor (chart -- bukan cuma tabel log) -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="font-bold text-base text-[#1b1c1c]">Tren Aktivitas Kompetitor</h2>
                        <p class="text-xs text-[#5f5e5e]">Jumlah konten baru terdeteksi per hari, 14 hari terakhir</p>
                    </div>
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.scrapelog.index') }}" class="text-xs text-[#885215] hover:underline shrink-0">Riwayat Lengkap →</a>
                    @endif
                </div>
                <div class="h-64">
                    <canvas id="trendActivityChart"></canvas>
                </div>
            </div>

            <!-- ROW 3: Chart Distribusi & Recent Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Chart Distribusi Platform (1/3 Lebar) -->
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d] flex flex-col justify-between">
                    <div>
                        <h2 class="font-bold text-base text-[#1b1c1c]">Distribusi Platform Target</h2>
                        <p class="text-xs text-[#5f5e5e] mb-4">Persentase media sosial kompetitor</p>
                    </div>
                    <div class="relative w-full aspect-square max-h-64 mx-auto flex items-center justify-center">
                        <canvas id="platformChart"></canvas>
                    </div>
                </div>

                <!-- Tabel Tren & Konten Terbaru (2/3 Lebar) -->
                <div class="lg:col-span-2 bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d] flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="font-bold text-base text-[#1b1c1c]">Tren & Konten Kompetitor Terbaru</h2>
                                <p class="text-xs text-[#5f5e5e]">Data postingan yang siap dikonsumsi AI</p>
                            </div>
                            @if(Auth::check() && Auth::user()->isAdmin())
                                <a href="{{ route('admin.datasource.index') }}" class="text-xs text-[#885215] hover:text-[#885215] hover:underline">Kelola Data →</a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-xs text-[#524439] border-b border-[#e5e5e1] uppercase bg-[#f5f3f3]">
                                        <th class="py-3 px-3">Target</th>
                                        <th class="py-3 px-3">Judul / Konten</th>
                                        <th class="py-3 px-3 text-right">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#e5e5e1]">
                                    @forelse($recentPosts as $post)
                                        <tr class="hover:bg-[#fbf9f8] transition">
                                            <td class="py-3 px-3 font-medium text-[#1b1c1c] shrink-0">
                                                <span class="inline-block px-2 py-0.5 text-[10px] rounded bg-[#885215]/10 text-[#885215] border border-[#885215]/20 mr-1">
                                                    {{ $post->trendSource->platform ?? 'General' }}
                                                </span>
                                                <span class="text-xs font-semibold">{{ $post->trendSource->name ?? 'Kompetitor' }}</span>
                                            </td>
                                            <td class="py-3 px-3 text-[#524439]">
                                                <div class="font-semibold text-xs text-[#885215] line-clamp-1">{{ $post->title }}</div>
                                                <div class="text-[11px] text-[#847467] line-clamp-1">{{ $post->content }}</div>
                                            </td>
                                            <td class="py-3 px-3 text-right text-xs text-[#5f5e5e] whitespace-nowrap">
                                                {{ $post->created_at->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-6 text-center text-xs text-[#847467]">
                                                Belum ada data postingan kompetitor. Tambahkan di menu Data Source.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ROW 4: Akses Cepat ke Fitur -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                <h2 class="font-bold text-base text-[#1b1c1c] mb-1">Fitur Lainnya</h2>
                <p class="text-xs text-[#5f5e5e] mb-4">Ringkasannya sudah di atas — untuk detail & kelola data, masuk ke fitur terkait.</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <a href="{{ route('chat.index') }}" class="flex flex-col items-center text-center gap-2 p-4 rounded-xl border border-[#e5e5e1] hover:border-[#885215]/40 hover:bg-[#fbf9f8] transition">
                        <div class="w-10 h-10 rounded-lg bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <span class="text-xs font-semibold text-[#1b1c1c]">Chat AI</span>
                    </a>

                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.datasource.index') }}" class="flex flex-col items-center text-center gap-2 p-4 rounded-xl border border-[#e5e5e1] hover:border-[#885215]/40 hover:bg-[#fbf9f8] transition">
                            <div class="w-10 h-10 rounded-lg bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-[#1b1c1c]">Kelola Kompetitor</span>
                        </a>

                        <a href="{{ route('admin.scrapelog.index') }}" class="flex flex-col items-center text-center gap-2 p-4 rounded-xl border border-[#e5e5e1] hover:border-[#885215]/40 hover:bg-[#fbf9f8] transition">
                            <div class="w-10 h-10 rounded-lg bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-[#1b1c1c]">Log Scraping</span>
                        </a>

                        <a href="{{ route('admin.meta-insights.index') }}" class="flex flex-col items-center text-center gap-2 p-4 rounded-xl border border-[#e5e5e1] hover:border-[#885215]/40 hover:bg-[#fbf9f8] transition">
                            <div class="w-10 h-10 rounded-lg bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-[#1b1c1c]">Performa Meta</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center text-center gap-2 p-4 rounded-xl border border-[#e5e5e1] hover:border-[#885215]/40 hover:bg-[#fbf9f8] transition">
                            <div class="w-10 h-10 rounded-lg bg-[#885215]/15 border border-[#885215]/30 flex items-center justify-center text-[#885215]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-[#1b1c1c]">Kelola User</span>
                        </a>
                    @endif
                </div>
            </div>

        </main>
    </div>

    <!-- Script Chart.js -->
    <script>
        const platformLabels = @json($platformDistribution->keys());
        const platformCounts = @json($platformDistribution->values());

        const ctx = document.getElementById('platformChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: platformLabels.length ? platformLabels : ['Belum Ada Data'],
                datasets: [{
                    data: platformCounts.length ? platformCounts : [1],
                    backgroundColor: [
                        '#885215', // Copper
                        '#c48444', // Warm tan
                        '#5f5e5e', // Charcoal
                        '#e5e5e1', // Light neutral
                        '#524439', // Dark brown
                        '#919291'  // Soft grey
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#524439',
                            font: { size: 11 }
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // Chart Tren Aktivitas Kompetitor (line, 14 hari terakhir)
        const trendLabels = @json($trendLabels);
        const trendCounts = @json($trendCounts);

        new Chart(document.getElementById('trendActivityChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Konten baru terdeteksi',
                    data: trendCounts,
                    borderColor: '#885215',
                    backgroundColor: 'rgba(136, 82, 21, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: '#885215',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.parsed.y} konten baru`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#847467', font: { size: 11 } },
                        grid: { color: '#f0efed' }
                    },
                    x: {
                        ticks: { color: '#847467', font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>