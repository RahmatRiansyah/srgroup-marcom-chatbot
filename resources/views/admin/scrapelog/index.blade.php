<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log Scraping - SR Group</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex font-sans overflow-hidden">

    <!-- 1. Include Sidebar Navigasi -->
    @include('components.sidebar')

    <!-- 2. Main Content Area -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">

        <!-- Top Header Bar -->
        <header class="bg-slate-800 border-b border-slate-700 py-4 px-6 flex justify-between items-center shadow-lg shrink-0">
            <div>
                <h1 class="font-bold text-lg text-white">Log Scraping</h1>
                <p class="text-xs text-slate-400">Riwayat & status pengambilan data harian dari mesin analisis Python</p>
            </div>

            <form action="{{ route('admin.scrapelog.run') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-md hover:shadow-indigo-500/20 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Jalankan Sekarang
                </button>
            </form>
        </header>

        <!-- Body Content -->
        <main class="p-6 max-w-7xl mx-auto w-full space-y-6">

            <!-- Alert Success -->
            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Info: cron -->
            <div class="bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-3 text-xs text-slate-400">
                Scraping otomatis dijadwalkan tiap hari jam 06:00. Pastikan server menjalankan
                <code class="text-slate-300 bg-slate-900 px-1.5 py-0.5 rounded">php artisan schedule:run</code>
                tiap menit lewat cron supaya jadwal ini benar-benar berjalan.
            </div>

            <!-- Tabel Riwayat -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-700">
                    <h3 class="text-base font-semibold text-white">Riwayat Scraping</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-300">
                        <thead class="text-xs uppercase bg-slate-700/50 text-slate-400">
                            <tr>
                                <th class="px-6 py-3.5">Waktu</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5">Target Diproses</th>
                                <th class="px-6 py-3.5">Berhasil</th>
                                <th class="px-6 py-3.5">Gagal</th>
                                <th class="px-6 py-3.5">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-700/30 transition align-top">
                                    <td class="px-6 py-4 text-slate-400 whitespace-nowrap">
                                        {{ $log->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($log->status === 'success')
                                            <span class="px-2.5 py-1 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">Berhasil</span>
                                        @elseif($log->status === 'partial')
                                            <span class="px-2.5 py-1 text-xs font-medium text-amber-400 bg-amber-500/10 border border-amber-500/20 rounded-lg">Sebagian</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-lg">Gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $log->total_targets }}</td>
                                    <td class="px-6 py-4 text-emerald-400 font-medium">{{ $log->success_count }}</td>
                                    <td class="px-6 py-4 text-rose-400 font-medium">{{ $log->failed_count }}</td>
                                    <td class="px-6 py-4 text-slate-400 max-w-sm">
                                        @if($log->message)
                                            <span>{{ $log->message }}</span>
                                        @elseif($log->details)
                                            @php
                                                $failedTargets = collect($log->details)->where('status', 'failed');
                                            @endphp
                                            @if($failedTargets->isNotEmpty())
                                                <span class="text-xs">
                                                    Gagal: {{ $failedTargets->pluck('name')->implode(', ') }}
                                                </span>
                                            @else
                                                <span class="text-slate-600">-</span>
                                            @endif
                                        @else
                                            <span class="text-slate-600">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-slate-500">
                                        Belum ada riwayat scraping. Klik "Jalankan Sekarang" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="p-4 border-t border-slate-700">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

</body>
</html>
