<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics - SR Group</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex font-sans overflow-hidden">

    <!-- Include Sidebar Navigation -->
    @include('components.sidebar')

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">

        <!-- Top Header Bar -->
        <header class="bg-slate-800 border-b border-slate-700 py-4 px-6 flex justify-between items-center shadow-lg shrink-0">
            <div>
                <h1 class="font-bold text-lg text-white">Dashboard Utama</h1>
                <p class="text-xs text-slate-400">Selamat datang kembali, <span class="text-indigo-400 font-semibold">{{ Auth::user()->name }}</span>!</p>
            </div>
        </header>

        <!-- Body Content -->
        <main class="p-6 max-w-7xl mx-auto w-full space-y-6">

            <!-- Summary Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase">AI Strategy Engine</p>
                        <h3 class="text-xl font-bold text-emerald-400 mt-1">Aktif & Ready</h3>
                    </div>
                    <div class="p-3 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase">Target Monitoring</p>
                        <h3 class="text-xl font-bold text-white mt-1">
                            {{ \App\Models\TrendSource::count() ?? 0 }} Target
                        </h3>
                    </div>
                    <div class="p-3 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase">Akses Pengguna</p>
                        <h3 class="text-xl font-bold text-white mt-1">Administrator</h3>
                    </div>
                    <div class="p-3 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Quick Action Cards / Fitur Utama -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-semibold text-white mb-4">Pintasan Fitur & Menu</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Fitur 1: AI Chatbot -->
                    <a href="{{ route('chat.index') }}" class="group bg-slate-900 border border-slate-700 hover:border-indigo-500 p-5 rounded-xl transition duration-200 block">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-indigo-600/20 text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white rounded-xl transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white group-hover:text-indigo-400 transition">Asisten AI Marcom</h3>
                                <p class="text-xs text-slate-400 mt-1">Konsultasikan strategi marketing, perancangan campaign, dan analisis tren kompetitor.</p>
                            </div>
                        </div>
                    </a>

                    <!-- Fitur 2: Target Kompetitor -->
                    <a href="{{ route('admin.datasource.index') }}" class="group bg-slate-900 border border-slate-700 hover:border-indigo-500 p-5 rounded-xl transition duration-200 block">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-indigo-600/20 text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white rounded-xl transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white group-hover:text-indigo-400 transition">Kelola Target & Kompetitor</h3>
                                <p class="text-xs text-slate-400 mt-1">Atur daftar URL, brand kompetitor, dan keyword yang akan dipantau oleh scraper.</p>
                            </div>
                        </div>
                    </a>

                </div>
            </div>

        </main>
    </div>

</body>
</html>