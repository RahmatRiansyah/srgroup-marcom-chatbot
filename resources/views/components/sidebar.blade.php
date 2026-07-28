<!-- Overlay gelap di mode mobile, klik buat nutup sidebar. md:hidden dobel-pasti supaya nggak pernah nongol di desktop walau class 'hidden' ke-toggle JS. -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

<aside id="app-sidebar" class="w-64 bg-[#1a1a1a] border-r border-[#353535] flex flex-col justify-between shrink-0 h-screen fixed md:static inset-y-0 left-0 z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="flex-1 flex flex-col overflow-y-auto min-h-0">
        <!-- Logo / App Header -->
        <div class="p-6 border-b border-[#353535] flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3">
                <x-application-logo class="w-10 h-10" />
                <div>
                    <h2 class="font-bold text-[#ffffff] text-base leading-tight">SR GROUP</h2>
                    <p class="text-xs text-[#c8c6c5]">PT Sritama Boga Prima • Marcom F&B</p>
                </div>
            </div>

            <!-- Tombol Tutup, cuma tampil di mode mobile -->
            <button type="button" onclick="toggleSidebar()" class="md:hidden text-[#c8c6c5] hover:text-[#ffffff] p-1.5 rounded-lg hover:bg-[#885215]/15 transition" aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Menu Navigation -->
        <nav class="p-4 space-y-6 flex-1">
            <!-- Menu Utama -->
            <div>
                <p class="text-[10px] font-bold text-[#c8c6c5] uppercase tracking-wider px-3 mb-2">MENU UTAMA</p>
                <div class="space-y-1">
                    
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-[#ffffff]/5 border-l-4 border-[#885215] text-[#ffffff] shadow-sm shadow-[#0000000d]' : 'text-[#c8c6c5] hover:bg-[#ffffff]/10 hover:text-[#ffffff]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Asisten AI Marcom -->
                    <a href="{{ route('chat.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('chat.*') ? 'bg-[#ffffff]/5 border-l-4 border-[#885215] text-[#ffffff] shadow-sm shadow-[#0000000d]' : 'text-[#c8c6c5] hover:bg-[#ffffff]/10 hover:text-[#ffffff]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Asisten AI Marcom</span>
                    </a>

                    <!-- Sub-Menu Riwayat Chat Gemini (Hanya tampil di halaman Chat) -->
                    @if(request()->routeIs('chat.*'))
                        <div class="pt-2 pl-2 pr-1 space-y-2 border-l-2 border-[#885215]/25 ml-4 my-2">
                            
                            <!-- Tombol Percakapan Baru -->
                            <form action="{{ route('chat.new') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-[#885215] hover:bg-[#a3692a] text-[#ffffff] font-medium py-2 px-3 rounded-lg flex items-center gap-2 transition text-xs border border-transparent">
                                    <svg class="w-4 h-4 text-[#ffffff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span>Percakapan baru</span>
                                </button>
                            </form>

                            <!-- Input Telusuri Percakapan -->
                            <form action="{{ route('chat.index') }}" method="GET" class="relative">
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Telusuri..." 
                                    class="w-full bg-[#252525] text-[#e5e5e1] text-xs rounded-lg pl-7 pr-2 py-1.5 border border-[#847467] focus:outline-none focus:border-[#885215]"
                                >
                                <svg class="w-3.5 h-3.5 text-[#885215] absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </form>

                            <!-- List Percakapan Terbaru -->
                            <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                <p class="text-[10px] font-semibold text-[#c8c6c5] px-1 py-0.5 uppercase">Terbaru</p>

                                @if(isset($sessions))
                                    @forelse($sessions as $sess)
                                        <div class="group flex items-center justify-between rounded-lg {{ isset($activeSession) && $activeSession->id === $sess->id ? 'bg-[#ffffff]/5 text-[#ffffff]' : 'text-[#c8c6c5] hover:bg-[#ffffff]/10' }}">
                                            <a href="{{ route('chat.show', $sess->id) }}" class="flex-1 px-2 py-1.5 text-xs truncate flex items-center gap-1.5">
                                                <svg class="w-3 h-3 shrink-0 text-[#885215]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                                <span class="truncate">{{ $sess->title }}</span>
                                            </a>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('chat.destroy', $sess->id) }}" method="POST" onsubmit="return confirm('Hapus percakapan ini?')" class="opacity-0 group-hover:opacity-100 pr-1 transition">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[#c8c6c5] hover:text-[#ffffff] text-xs px-1">
                                                    &times;
                                                </button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="text-[11px] text-[#c8c6c5] px-1 italic">Belum ada percakapan</p>
                                    @endforelse
                                @endif
                            </div>

                        </div>
                    @endif

                    <!-- Menu Admin (cuma tampil untuk role admin) -->
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <!-- Data Target / Kompetitor -->
                        <a href="{{ route('admin.datasource.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.datasource.*') ? 'bg-[#ffffff]/5 border-l-4 border-[#885215] text-[#ffffff] shadow-sm shadow-[#0000000d]' : 'text-[#c8c6c5] hover:bg-[#ffffff]/10 hover:text-[#ffffff]' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Data Target / Kompetitor</span>
                        </a>

                        <!-- Log Scraping / Scheduler -->
                        <a href="{{ route('admin.scrapelog.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.scrapelog.*') ? 'bg-[#ffffff]/5 border-l-4 border-[#885215] text-[#ffffff] shadow-sm shadow-[#0000000d]' : 'text-[#c8c6c5] hover:bg-[#ffffff]/10 hover:text-[#ffffff]' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Log Scraping</span>
                        </a>

                        <!-- Performa Meta (engagement akun IG/FB sendiri, real-time via Graph API) -->
                        <a href="{{ route('admin.meta-insights.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.meta-insights.*') ? 'bg-[#ffffff]/5 border-l-4 border-[#885215] text-[#ffffff] shadow-sm shadow-[#0000000d]' : 'text-[#c8c6c5] hover:bg-[#ffffff]/10 hover:text-[#ffffff]' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Performa Meta</span>
                        </a>
                    @endif

                </div>
            </div>

            <!-- Pengaturan -->
            <div>
                <p class="text-[10px] font-bold text-[#c8c6c5] uppercase tracking-wider px-3 mb-2">PENGATURAN</p>
                <div class="space-y-1">
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-[#ffffff]/5 border-l-4 border-[#885215] text-[#ffffff] shadow-sm shadow-[#0000000d]' : 'text-[#c8c6c5] hover:bg-[#ffffff]/10 hover:text-[#ffffff]' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.65"/></svg>
                            <span>Kelola User</span>
                        </a>
                    @endif

                </div>
            </div>
        </nav>
    </div>

    <!-- User Profile & Logout Section -->
<div class="p-4 border-t border-[#353535] shrink-0">
            <div class="flex items-center justify-between p-2">
                <div class="truncate">
                    <p class="text-sm font-semibold text-[#ffffff] truncate">{{ Auth::user()->name ?? 'Admin Marcom' }}</p>
                    <p class="text-xs text-[#c8c6c5] truncate flex items-center gap-1.5">
                        {{ Auth::user()->email ?? 'admin@srgroup.com' }}
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <span class="bg-[#ffffff]/10 text-[#885215] border border-[#847467] px-1.5 py-0.5 rounded text-[9px] font-bold uppercase shrink-0">Admin</span>
                    @endif
                </p>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="text-[#e5e5e1] hover:text-[#ffffff] p-2 rounded-lg hover:bg-[#ffffff]/10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<script>
    // Toggle drawer sidebar di mode mobile (dipanggil dari tombol hamburger
    // di header tiap halaman & tombol X/overlay di dalam sidebar sendiri).
    // Sengaja pakai transform (bukan hidden/block) supaya animasi geser
    // (transition-transform) di <aside> jalan mulus.
    function toggleSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar) sidebar.classList.toggle('-translate-x-full');
        if (overlay) overlay.classList.toggle('hidden');
    }
    window.toggleSidebar = toggleSidebar;

    // Kalau layar di-resize/rotate dari potrait ke ukuran desktop (md ke atas)
    // sementara drawer lagi kebuka, pastikan overlay ikut ketutup -- di
    // desktop sidebar-nya statis (md:translate-x-0), jadi overlay gelap tidak
    // boleh nyangkut menutupi konten.
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 768) {
            document.getElementById('sidebar-overlay')?.classList.add('hidden');
        }
    });
</script>