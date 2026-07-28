<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log Scraping - SR Group</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-white.svg') }}">
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fbf9f8] text-[#1b1c1c] min-h-screen flex font-sans overflow-hidden">

    <!-- 1. Include Sidebar Navigasi -->
    @include('components.sidebar')

    <!-- 2. Main Content Area -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">

        <!-- Top Header Bar -->
        <header class="bg-[#ffffff] border-b border-[#e5e5e1] py-4 px-6 flex justify-between items-center shadow-sm shadow-[#0000000d] shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden shrink-0 text-[#1b1c1c] hover:text-[#1b1c1c] bg-[#fbf9f8] hover:bg-[#f5f3f3] p-2 rounded-lg transition" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="font-bold text-lg text-[#1b1c1c]">Log Scraping</h1>
                    <p class="text-xs text-[#524439]">Riwayat & status pengambilan data harian dari mesin analisis Python</p>
                </div>
            </div>

            <form action="{{ route('admin.scrapelog.run') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="bg-[#885215] hover:bg-[#784a15] text-[#ffffff] font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-sm shadow-[#885215]/20 flex items-center gap-2"
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
                <div class="bg-[#8CCEAD]/10 border border-[#8CCEAD]/20 text-[#8CCEAD] px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Alert Warning (mis. klik "Jalankan Sekarang" saat proses sebelumnya masih berjalan) -->
            @if(session('warning'))
                <div class="bg-[#f4e3d6] border border-[#885215]/20 text-[#885215] px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <!-- Info: cron & queue worker -->
            <div class="bg-[#fbf9f8] border border-[#e5e5e1] rounded-xl px-4 py-3 text-xs text-[#524439] space-y-1">
                <p>
                    Scraping otomatis dijadwalkan tiap hari jam 06:00. Pastikan server menjalankan
                    <code class="text-[#1b1c1c] bg-[#ffffff] px-1.5 py-0.5 rounded">php artisan schedule:run</code>
                    tiap menit lewat cron supaya jadwal ini benar-benar berjalan.
                </p>
                <p>
                    Tombol "Jalankan Sekarang" berjalan di background lewat queue -- pastikan juga ada proses
                    <code class="text-[#1b1c1c] bg-[#ffffff] px-1.5 py-0.5 rounded">php artisan queue:work</code>
                    yang aktif, kalau tidak job-nya cuma akan menumpuk & tidak pernah diproses.
                </p>
            </div>

            <!-- Tabel Riwayat -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl overflow-hidden shadow-sm shadow-[#0000000d]">
                <div class="p-4 border-b border-[#e5e5e1]">
                    <h3 class="text-base font-semibold text-[#1b1c1c]">Riwayat Scraping</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#524439]">
                        <thead class="text-xs uppercase bg-[#f5f3f3] text-[#524439]">
                            <tr>
                                <th class="px-6 py-3.5">Waktu</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5">Target Diproses</th>
                                <th class="px-6 py-3.5">Berhasil (Baru)</th>
                                <th class="px-6 py-3.5">Tanpa Perubahan</th>
                                <th class="px-6 py-3.5">Gagal</th>
                                <th class="px-6 py-3.5">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e5e1]">
                            @forelse($logs as $log)
                                <tr class="hover:bg-[#fbf9f8] transition align-top">
                                    <td class="px-6 py-4 text-[#885215] whitespace-nowrap">
                                        {{ $log->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($log->status === 'success')
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#8CCEAD] bg-[#8CCEAD]/10 border border-[#8CCEAD]/20 rounded-lg">Berhasil</span>
                                        @elseif($log->status === 'partial')
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#885215] bg-[#f4e3d6] border border-[#e7c5a6] rounded-lg">Sebagian</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#885215] bg-[#f4e3d6] border border-[#e7c5a6] rounded-lg">Gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $log->total_targets }}</td>
                                    <td class="px-6 py-4 text-[#1b1c1c] font-medium">{{ $log->success_count }}</td>
                                    <td class="px-6 py-4 text-[#524439] font-medium">{{ $log->unchanged_count }}</td>
                                    <td class="px-6 py-4 text-[#885215] font-medium">{{ $log->failed_count }}</td>
                                    <td class="px-6 py-4 text-[#524439] max-w-sm">
                                        @if($log->message)
                                            <span>{{ $log->message }}</span>
                                        @elseif($log->details)
                                            @php
                                                $failedTargets = collect($log->details)->where('status', 'failed');
                                            @endphp
                                            @if($failedTargets->isNotEmpty())
                                                <span class="text-xs text-[#524439]">
                                                    Gagal: {{ $failedTargets->pluck('name')->implode(', ') }}
                                                </span>
                                            @else
                                                <span class="text-[#847467]">-</span>
                                            @endif
                                        @else
                                            <span class="text-[#847467]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-6 text-center text-[#847467]">
                                        Belum ada riwayat scraping. Klik "Jalankan Sekarang" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="p-4 border-t border-[#e5e5e1]">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

</body>
</html>
