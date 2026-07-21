<aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col justify-between shrink-0 hidden md:flex h-screen">
    <div class="flex-1 flex flex-col overflow-y-auto min-h-0">
        <!-- Logo / App Header -->
        <div class="p-6 border-b border-slate-700 flex items-center space-x-3 shrink-0">
            <div class="bg-indigo-600 text-white font-bold p-2.5 rounded-xl shadow-lg shadow-indigo-500/30">
                SR
            </div>
            <div>
                <h2 class="font-bold text-white text-base leading-tight">SR GROUP</h2>
                <p class="text-xs text-slate-400">Marcom Analytics & AI</p>
            </div>
        </div>

        <!-- Menu Navigation -->
        <nav class="p-4 space-y-6 flex-1">
            <!-- Menu Utama -->
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">MENU UTAMA</p>
                <div class="space-y-1">
                    
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Asisten AI Marcom -->
                    <a href="{{ route('chat.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('chat.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Asisten AI Marcom</span>
                    </a>

                    <!-- Sub-Menu Riwayat Chat Gemini (Hanya tampil di halaman Chat) -->
                    @if(request()->routeIs('chat.*'))
                        <div class="pt-2 pl-2 pr-1 space-y-2 border-l-2 border-slate-700 ml-4 my-2">
                            
                            <!-- Tombol Percakapan Baru -->
                            <form action="{{ route('chat.new') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-slate-700/70 hover:bg-slate-700 text-slate-200 font-medium py-2 px-3 rounded-lg flex items-center gap-2 transition text-xs border border-slate-600/40">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
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
                                    class="w-full bg-slate-900/80 text-slate-200 text-xs rounded-lg pl-7 pr-2 py-1.5 border border-slate-700 focus:outline-none focus:border-indigo-500"
                                >
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </form>

                            <!-- List Percakapan Terbaru -->
                            <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                <p class="text-[10px] font-semibold text-slate-400 px-1 py-0.5 uppercase">Terbaru</p>

                                @if(isset($sessions))
                                    @forelse($sessions as $sess)
                                        <div class="group flex items-center justify-between rounded-lg hover:bg-slate-700/50 {{ isset($activeSession) && $activeSession->id === $sess->id ? 'bg-slate-700 text-white' : 'text-slate-400' }}">
                                            <a href="{{ route('chat.show', $sess->id) }}" class="flex-1 px-2 py-1.5 text-xs truncate flex items-center gap-1.5">
                                                <svg class="w-3 h-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                                <span class="truncate">{{ $sess->title }}</span>
                                            </a>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('chat.destroy', $sess->id) }}" method="POST" onsubmit="return confirm('Hapus percakapan ini?')" class="opacity-0 group-hover:opacity-100 pr-1 transition">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-400 hover:text-rose-400 text-xs px-1">
                                                    &times;
                                                </button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="text-[11px] text-slate-500 px-1 italic">Belum ada percakapan</p>
                                    @endforelse
                                @endif
                            </div>

                        </div>
                    @endif

                    <!-- Data Target / Kompetitor -->
                    <a href="{{ route('admin.datasource.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.datasource.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Data Target / Kompetitor</span>
                    </a>

                </div>
            </div>

            <!-- Pengaturan -->
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">PENGATURAN</p>
                <div class="space-y-1">
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('register') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>Register User Baru</span>
                    </a>
                    @endif
                </div>
            </div>
        </nav>
    </div>

    <!-- User Profile & Logout Section -->
    <div class="p-4 border-t border-slate-700 shrink-0">
        <div class="flex items-center justify-between p-2">
            <div class="truncate">
                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'Admin Marcom' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? 'admin@srgroup.com' }}</p>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="text-slate-400 hover:text-rose-400 p-2 rounded-lg hover:bg-slate-700/50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>