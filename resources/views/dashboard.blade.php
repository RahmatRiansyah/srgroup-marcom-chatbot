<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics - SR Group</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CDN Chart.js untuk Visualisasi Grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex font-sans overflow-x-hidden">

    <!-- 1. Sidebar Navigasi -->
    @include('components.sidebar')

    <!-- 2. Area Utama Dashboard -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">
        
        <!-- Header -->
        <header class="bg-slate-800 border-b border-slate-700 py-4 px-6 flex justify-between items-center shadow-lg shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden shrink-0 text-slate-300 hover:text-white bg-slate-700/60 hover:bg-slate-700 p-2 rounded-lg transition" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="font-bold text-xl text-white">Marcom Analytics Dashboard</h1>
                    <p class="text-xs text-slate-400">Ringkasan Performa Target, Kompetitor & Sesi AI</p>
                </div>
            </div>
            <div class="text-xs text-slate-400 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-700">
                Terakhir Diperbarui: <span class="text-indigo-400 font-semibold">{{ date('d M Y') }}</span>
            </div>
        </header>

        <!-- Main Content Grid -->
        <main class="p-6 space-y-6 max-w-7xl mx-auto w-full">

            <!-- ROW 1: Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- Card 1: Total Kompetitor -->
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 flex items-center justify-between shadow-sm hover:border-indigo-500/50 transition">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Target / Kompetitor</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1">{{ $totalSources }}</h3>
                        <p class="text-xs text-emerald-400 mt-2 font-medium">✓ Terdaftar di sistem</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>

                <!-- Card 2: Total Postingan Tren -->
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 flex items-center justify-between shadow-sm hover:border-indigo-500/50 transition">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Konten & Tren Disimpan</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1">{{ $totalPosts }}</h3>
                        <p class="text-xs text-indigo-400 mt-2 font-medium">Data rujukan AI</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                </div>

                <!-- Card 3: Total Sesi Chat AI -->
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 flex items-center justify-between shadow-sm hover:border-indigo-500/50 transition">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Percakapan AI</p>
                        <h3 class="text-3xl font-extrabold text-white mt-1">{{ $totalChats }}</h3>
                        <p class="text-xs text-indigo-400 mt-2 font-medium">Sesi analisis aktif</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-600/20 border border-purple-500/30 flex items-center justify-center text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                </div>

            </div>

            <!-- ROW 2: Chart & Recent Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Chart Distribusi Platform (1/3 Lebar) -->
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="font-bold text-base text-white">Distribusi Platform Target</h2>
                        <p class="text-xs text-slate-400 mb-4">Persentase media sosial kompetitor</p>
                    </div>
                    <div class="relative w-full aspect-square max-h-64 mx-auto flex items-center justify-center">
                        <canvas id="platformChart"></canvas>
                    </div>
                </div>

                <!-- Tabel Tren & Konten Terbaru (2/3 Lebar) -->
                <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="font-bold text-base text-white">Tren & Konten Kompetitor Terbaru</h2>
                                <p class="text-xs text-slate-400">Data postingan yang siap dikonsumsi AI</p>
                            </div>
                            <a href="{{ route('admin.datasource.index') }}" class="text-xs text-indigo-400 hover:underline">Kelola Data →</a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-xs text-slate-400 border-b border-slate-700 uppercase bg-slate-900/40">
                                        <th class="py-3 px-3">Target</th>
                                        <th class="py-3 px-3">Judul / Konten</th>
                                        <th class="py-3 px-3 text-right">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50">
                                    @forelse($recentPosts as $post)
                                        <tr class="hover:bg-slate-700/30 transition">
                                            <td class="py-3 px-3 font-medium text-slate-200 shrink-0">
                                                <span class="inline-block px-2 py-0.5 text-[10px] rounded bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 mr-1">
                                                    {{ $post->trendSource->platform ?? 'General' }}
                                                </span>
                                                <span class="text-xs font-semibold">{{ $post->trendSource->name ?? 'Kompetitor' }}</span>
                                            </td>
                                            <td class="py-3 px-3 text-slate-300">
                                                <div class="font-semibold text-xs text-indigo-200 line-clamp-1">{{ $post->title }}</div>
                                                <div class="text-[11px] text-slate-400 line-clamp-1">{{ $post->content }}</div>
                                            </td>
                                            <td class="py-3 px-3 text-right text-xs text-slate-400 whitespace-nowrap">
                                                {{ $post->created_at->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-6 text-center text-xs text-slate-500">
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
                        '#6366f1', // Indigo
                        '#10b981', // Emerald
                        '#f59e0b', // Amber
                        '#ec4899', // Pink
                        '#3b82f6', // Blue
                        '#8b5cf6'  // Purple
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
                            color: '#94a3b8',
                            font: { size: 11 }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>