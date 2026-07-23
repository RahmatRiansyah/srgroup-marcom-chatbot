<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Sumber Data Marcom - SR Group</title>
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
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden shrink-0 text-slate-300 hover:text-white bg-slate-700/60 hover:bg-slate-700 p-2 rounded-lg transition" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="font-bold text-lg text-white">Manajemen Sumber Data Marcom</h1>
                    <p class="text-xs text-slate-400">Kelola target kompetitor dan keyword yang dipantau oleh sistem</p>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <main class="p-6 max-w-7xl mx-auto w-full space-y-6">

            <!-- Alert Success -->
            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Form Tambah Sumber Data -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-xl">
                <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Tambah Target Kompetitor / Keyword
                </h3>

                <form action="{{ route('admin.datasource.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-2">Nama Kompetitor / Keyword</label>
                        <input 
                            type="text" 
                            name="name" 
                            placeholder="Misal: Mixue" 
                            required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500 transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-2">Platform</label>
                        <select 
                            name="platform" 
                            required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500 transition"
                        >
                            <option value="Website">Website</option>
                            <option value="Google Trends">Google Trends</option>
                            <option value="Instagram">Instagram</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-2">URL Target (Opsional)</label>
                        <input 
                            type="url" 
                            name="source_url" 
                            placeholder="https://example.com"
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500 transition"
                        >
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button 
                            type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-md hover:shadow-indigo-500/20"
                        >
                            Simpan Target
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Sumber Data -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-700">
                    <h3 class="text-base font-semibold text-white">Daftar Monitoring Aktif</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-300">
                        <thead class="text-xs uppercase bg-slate-700/50 text-slate-400">
                            <tr>
                                <th class="px-6 py-3.5">Nama / Keyword</th>
                                <th class="px-6 py-3.5">Platform</th>
                                <th class="px-6 py-3.5">URL</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($sources as $source)
                                <tr class="hover:bg-slate-700/30 transition">
                                    <td class="px-6 py-4 font-semibold text-white">
                                        {{ $source->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-2.5 py-1 rounded-lg text-xs font-medium">
                                            {{ $source->platform }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($source->source_url)
                                            <a href="{{ $source->source_url }}" target="_blank" class="text-indigo-400 hover:underline truncate inline-block max-w-xs">
                                                {{ $source->source_url }}
                                            </a>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                                            Aktif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <!-- Form Hapus Target -->
                                        <form action="{{ route('admin.datasource.destroy', $source->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus target ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 bg-rose-500/10 border border-rose-500/20 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-slate-500">
                                        Belum ada data source yang didaftarkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>
</html>