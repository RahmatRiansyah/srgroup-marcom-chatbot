<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Performa Meta - SR Group</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-white.svg') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#fbf9f8] text-[#1b1c1c] min-h-screen flex font-sans overflow-hidden">

    @include('components.sidebar')

    <div class="flex-1 flex flex-col h-screen overflow-y-auto">

        <!-- Top Header Bar -->
        <header class="bg-[#ffffff] border-b border-[#e5e5e1] py-4 px-6 flex justify-between items-center shadow-sm shadow-[#0000000d] shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden shrink-0 text-[#1b1c1c] bg-[#fbf9f8] hover:bg-[#f5f3f3] p-2 rounded-lg transition" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="font-bold text-lg text-[#1b1c1c]">Performa Meta</h1>
                    <p class="text-xs text-[#524439]">Engagement akun Instagram/Facebook resmi SR Group, ditarik langsung dari Meta Graph API</p>
                </div>
            </div>

            <form action="{{ route('admin.meta-insights.sync') }}" method="POST">
                @csrf
                <button type="submit" class="bg-[#885215] hover:bg-[#784a15] text-[#ffffff] font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-sm shadow-[#885215]/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Sekarang
                </button>
            </form>
        </header>

        <main class="p-6 max-w-7xl mx-auto w-full space-y-6">

            @if(session('success'))
                <div class="bg-[#8CCEAD]/10 border border-[#8CCEAD]/20 text-[#8CCEAD] px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="bg-[#f4e3d6] border border-[#885215]/20 text-[#885215] px-4 py-3 rounded-xl text-sm">{{ session('warning') }}</div>
            @endif

            @if(!$latestSnapshot)
                <div class="bg-[#f4e3d6] border border-[#885215]/20 text-[#885215] px-4 py-3 rounded-xl text-sm">
                    Belum ada data sync. Pastikan <code class="bg-[#ffffff] px-1.5 py-0.5 rounded">META_ACCESS_TOKEN</code> &
                    <code class="bg-[#ffffff] px-1.5 py-0.5 rounded">META_IG_BUSINESS_ID</code> sudah diisi di <code class="bg-[#ffffff] px-1.5 py-0.5 rounded">.env</code>
                    service Python, lalu klik "Sync Sekarang".
                </div>
            @endif

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                    <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Followers</p>
                    <h3 class="text-2xl font-extrabold text-[#1b1c1c] mt-1">{{ $latestSnapshot?->followers_count ?? '—' }}</h3>
                    <p class="text-xs text-[#5f5e5e] mt-2 font-medium">@{{ $latestSnapshot?->username ?? 'belum di-sync' }}</p>
                </div>
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                    <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Rata-rata Engagement Rate</p>
                    <h3 class="text-2xl font-extrabold text-[#1b1c1c] mt-1">{{ $avgEngagementRate ? number_format($avgEngagementRate, 2) . '%' : '—' }}</h3>
                    <p class="text-xs text-[#5f5e5e] mt-2 font-medium">(like+komentar+saved+shares) / reach</p>
                </div>
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                    <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Post Terbaik</p>
                    <h3 class="text-2xl font-extrabold text-[#1b1c1c] mt-1">
                        {{ $bestPost?->engagement_rate_reach ? number_format($bestPost->engagement_rate_reach, 2) . '%' : '—' }}
                    </h3>
                    <p class="text-xs text-[#5f5e5e] mt-2 font-medium truncate">{{ $bestPost?->caption ? \Illuminate\Support\Str::limit($bestPost->caption, 30) : '—' }}</p>
                </div>
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                    <p class="text-xs font-medium text-[#524439] uppercase tracking-wider">Data Terakhir Disinkron</p>
                    <h3 class="text-lg font-extrabold text-[#1b1c1c] mt-1">
                        {{ $lastSyncedAt ? \Illuminate\Support\Carbon::parse($lastSyncedAt)->diffForHumans() : '—' }}
                    </h3>
                    <p class="text-xs text-[#5f5e5e] mt-2 font-medium">Auto-sync tiap 30 menit</p>
                </div>
            </div>

            <!-- Chart Engagement per Post -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-5 shadow-sm shadow-[#0000000d]">
                <h2 class="font-bold text-base text-[#1b1c1c] mb-1">Engagement Rate per Post (Terbaru → Terlama)</h2>
                <p class="text-xs text-[#5f5e5e] mb-4">Dihitung dari interaksi (like+komentar+saved+shares) dibanding reach tiap post</p>
                <div class="h-64">
                    <canvas id="engagementChart"></canvas>
                </div>
            </div>

            <!-- Tabel Post -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl overflow-hidden shadow-sm shadow-[#0000000d]">
                <div class="p-4 border-b border-[#e5e5e1]">
                    <h3 class="text-base font-semibold text-[#1b1c1c]">Post Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#524439]">
                        <thead class="text-xs uppercase bg-[#f5f3f3] text-[#524439]">
                            <tr>
                                <th class="px-6 py-3.5">Post</th>
                                <th class="px-6 py-3.5">Tanggal</th>
                                <th class="px-6 py-3.5 text-right">Like</th>
                                <th class="px-6 py-3.5 text-right">Komentar</th>
                                <th class="px-6 py-3.5 text-right">Saved</th>
                                <th class="px-6 py-3.5 text-right">Shares</th>
                                <th class="px-6 py-3.5 text-right">Reach</th>
                                <th class="px-6 py-3.5 text-right">Engagement Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e5e1]">
                            @forelse($posts as $post)
                                <tr class="hover:bg-[#fbf9f8] transition">
                                    <td class="px-6 py-4 max-w-xs">
                                        <a href="{{ $post->permalink }}" target="_blank" class="font-medium text-[#885215] hover:underline line-clamp-1">
                                            {{ \Illuminate\Support\Str::limit($post->caption ?? '(tanpa caption)', 50) }}
                                        </a>
                                        <span class="text-[10px] text-[#847467] uppercase">{{ $post->media_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $post->posted_at?->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($post->likes) }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($post->comments) }}</td>
                                    <td class="px-6 py-4 text-right">{{ $post->saved !== null ? number_format($post->saved) : '—' }}</td>
                                    <td class="px-6 py-4 text-right">{{ $post->shares !== null ? number_format($post->shares) : '—' }}</td>
                                    <td class="px-6 py-4 text-right">{{ $post->reach !== null ? number_format($post->reach) : '—' }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-[#1b1c1c]">
                                        {{ $post->engagement_rate_reach ? number_format($post->engagement_rate_reach, 2) . '%' : ($post->engagement_rate_followers ? number_format($post->engagement_rate_followers, 2) . '%*' : '—') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-6 text-center text-[#847467]">
                                        Belum ada data post. Klik "Sync Sekarang" setelah kredensial Meta diisi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($posts->hasPages())
                    <div class="p-4 border-t border-[#e5e5e1]">{{ $posts->links() }}</div>
                @endif
                <p class="px-6 py-3 text-[11px] text-[#847467] border-t border-[#e5e5e1]">*dihitung dari followers, dipakai kalau data reach belum tersedia untuk post tsb</p>
            </div>

            <!-- Riwayat Sync -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl overflow-hidden shadow-sm shadow-[#0000000d]">
                <div class="p-4 border-b border-[#e5e5e1]">
                    <h3 class="text-base font-semibold text-[#1b1c1c]">Riwayat Sync</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#524439]">
                        <thead class="text-xs uppercase bg-[#f5f3f3] text-[#524439]">
                            <tr>
                                <th class="px-6 py-3.5">Waktu</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5">Ter-sync</th>
                                <th class="px-6 py-3.5">Gagal</th>
                                <th class="px-6 py-3.5">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e5e1]">
                            @forelse($syncLogs as $log)
                                <tr class="hover:bg-[#fbf9f8] transition">
                                    <td class="px-6 py-4 text-[#885215] whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4">
                                        @if($log->status === 'success')
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#8CCEAD] bg-[#8CCEAD]/10 border border-[#8CCEAD]/20 rounded-lg">Berhasil</span>
                                        @elseif($log->status === 'partial')
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#885215] bg-[#f4e3d6] border border-[#e7c5a6] rounded-lg">Sebagian</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#885215] bg-[#f4e3d6] border border-[#e7c5a6] rounded-lg">Gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $log->posts_synced }}</td>
                                    <td class="px-6 py-4">{{ $log->posts_failed }}</td>
                                    <td class="px-6 py-4 text-[#524439] max-w-sm">{{ $log->message ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-6 text-center text-[#847467]">Belum ada riwayat sync.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script>
        const posts = @json($posts->items());
        // Urutkan lama -> baru biar chart kebaca dari kiri ke kanan sebagai timeline
        const chartData = [...posts].reverse();

        const ctx = document.getElementById('engagementChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(p => p.posted_at ? new Date(p.posted_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) : '-'),
                datasets: [{
                    label: 'Engagement Rate (%)',
                    data: chartData.map(p => p.engagement_rate_reach ?? p.engagement_rate_followers ?? 0),
                    borderColor: '#885215',
                    backgroundColor: '#88521522',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { color: '#524439' } },
                    x: { ticks: { color: '#524439' } },
                }
            }
        });
    </script>
</body>
</html>