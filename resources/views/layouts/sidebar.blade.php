<aside class="w-64 border-r border-[#262626] min-h-screen flex flex-col justify-between shrink-0 text-white relative shadow-2xl" style="
    background-color: #0f0f0f;
    background-image: url('https://www.transparenttextures.com/patterns/dark-denim-3.png');
    background-repeat: repeat;
">
    <div>
        <!-- Brand Header -->
        <div class="p-5 border-b border-[#2a2a2a]/80 flex items-center space-x-3 bg-black/60 backdrop-blur-xs">
            <x-application-logo class="w-10 h-10" />
            <div>
                <h2 class="font-bold text-white text-sm tracking-wide">SR GROUP</h2>
                <p class="text-xs text-[#d4a359]">Marcom Analytics & AI</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1.5">
            <p class="px-3 text-[11px] font-bold text-[#d4a359] uppercase tracking-widest mb-3">Menu Utama</p>

            <!-- 1. Tombol Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#885215] text-white shadow-lg shadow-[#885215]/40 border-l-4 border-[#e5a84b]' : 'text-zinc-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>

            <!-- 2. Tombol Chatbot AI -->
            <a href="{{ route('chat.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('chat.*') ? 'bg-[#885215] text-white shadow-lg shadow-[#885215]/40 border-l-4 border-[#e5a84b]' : 'text-zinc-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span>Asisten AI Marcom</span>
            </a>

            <!-- 3. Tombol Admin Data Source (Kompetitor) -->
            <a href="{{ Route::has('sources.index') ? route('sources.index') : '#' }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('sources.*') ? 'bg-[#885215] text-white shadow-lg shadow-[#885215]/40 border-l-4 border-[#e5a84b]' : 'text-zinc-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Data Target / Kompetitor</span>
            </a>

            <p class="px-3 text-[11px] font-bold text-[#d4a359] uppercase tracking-widest mt-6 mb-3">Pengaturan</p>

            <!-- 4. Tombol Register / Manajemen User -->
            @if(Route::has('register'))
            <a href="{{ route('register') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('register') ? 'bg-[#885215] text-white shadow-lg shadow-[#885215]/40 border-l-4 border-[#e5a84b]' : 'text-zinc-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Register User Baru</span>
            </a>
            @endif
        </nav>
    </div>

    <!-- Profil Pengguna Singkat -->
    <div class="p-4 border-t border-[#2a2a2a]/80 bg-black/60 backdrop-blur-xs">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-[#885215] border border-[#d4a359]/40 flex items-center justify-center font-bold text-xs text-white shadow-md">
                ADM
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-semibold text-white truncate">
                    {{ Auth::user()->name ?? 'Admin Marcom' }}
                </p>
                <p class="text-[10px] text-zinc-400 truncate">
                    {{ Auth::user()->email ?? 'admin@srgroup.com' }}
                </p>
            </div>
        </div>
    </div>
</aside>