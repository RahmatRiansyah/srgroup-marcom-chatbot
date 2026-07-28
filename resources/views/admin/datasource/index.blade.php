<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Sumber Data Marcom - SR Group</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-white.svg') }}">

    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- CDN SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h1 class="font-bold text-lg text-[#1b1c1c]">Manajemen Sumber Data Marcom</h1>
                    <p class="text-xs text-[#524439]">Kelola target kompetitor dan keyword yang dipantau oleh sistem</p>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <main class="p-6 max-w-7xl mx-auto w-full space-y-6">

            <!-- Alert Success / Error -->
            @if(session('success'))
                <div class="bg-[#f4e9de] border border-[#e7c5a6] text-[#885215] px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-[#f4e3d6] border border-[#e7c5a6] text-[#885215] px-4 py-3 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Form Tambah Sumber Data -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-6 shadow-sm shadow-[#0000000d]">
                <h3 class="text-base font-semibold text-[#1b1c1c] mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#885215]"></span>
                    Tambah Target Kompetitor / Keyword
                </h3>

                <form action="{{ route('admin.datasource.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-[#524439] mb-2">Nama Kompetitor / Keyword</label>
                        <input 
                            type="text" 
                            name="name" 
                            placeholder="Misal: Mixue" 
                            required
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#524439] mb-2">Platform</label>
                        <select 
                            name="platform" 
                            required
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                            <option value="Website">Website</option>
                            <option value="Google Trends">Google Trends</option>
                            <option value="Instagram">Instagram</option>
                            <option value="TikTok">TikTok</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#524439] mb-2">URL Target (Opsional)</label>
                        <input 
                            type="url" 
                            name="source_url" 
                            placeholder="https://example.com"
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button 
                            type="submit" 
                            class="bg-[#885215] hover:bg-[#784a15] text-[#ffffff] font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-sm shadow-[#885215]/20"
                        >
                            Simpan Target
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Sumber Data -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl overflow-hidden shadow-sm shadow-[#0000000d]">
                <div class="p-4 border-b border-[#e5e5e1]">
                    <h3 class="text-base font-semibold text-[#1b1c1c]">Daftar Monitoring Aktif</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#524439]">
                        <thead class="text-xs uppercase bg-[#f5f3f3] text-[#524439]">
                            <tr>
                                <th class="px-6 py-3.5">Nama / Keyword</th>
                                <th class="px-6 py-[#3.5]">Platform</th>
                                <th class="px-6 py-3.5">URL</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e5e1]">
                            @forelse($sources as $source)
                                <tr class="hover:bg-[#fbf9f8] transition">
                                    <td class="px-6 py-4 font-semibold text-[#1b1c1c]">
                                        {{ $source->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-[#885215]/10 text-[#885215] border border-[#885215]/20 px-2.5 py-1 rounded-lg text-xs font-medium">
                                            {{ $source->platform }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($source->source_url)
                                            <a href="{{ $source->source_url }}" target="_blank" class="text-[#885215] hover:text-[#1b1c1c] hover:underline truncate inline-block max-w-xs">
                                                {{ $source->source_url }}
                                            </a>
                                        @else
                                            <span class="text-[#847467]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-medium text-[#2d6d3a] bg-[#d7f1dd] border border-[#b4e5c2] rounded-lg">
                                            Aktif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <!-- Form Hapus Target -->
                                        <form id="form-delete-{{ $source->id }}" action="{{ route('admin.datasource.destroy', $source->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="button" 
                                                onclick="konfirmasiHapus('{{ $source->name }}', 'form-delete-{{ $source->id }}')" 
                                                class="text-[#885215] hover:text-[#1b1c1c] hover:bg-[#885215]/10 bg-[#885215]/10 border border-[#885215]/20 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-[#847467]">
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

    <!-- Script SweetAlert2 Konfirmasi Hapus Target -->
    <script>
        function konfirmasiHapus(namaTarget, formId) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Apakah Anda yakin ingin menghapus target monitoring <strong>"${namaTarget}"</strong>?`,
                icon: 'warning',
                iconColor: '#885215',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#f5f3f3',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                background: '#ffffff',
                color: '#1b1c1c',
                customClass: {
                    popup: 'rounded-2xl border border-[#e5e5e1] shadow-xl p-6',
                    title: 'text-lg font-bold text-[#1b1c1c]',
                    htmlContainer: 'text-sm text-[#5f5e5e] mt-2',
                    confirmButton: 'px-4 py-2 text-xs font-semibold rounded-xl text-white transition shadow-sm',
                    cancelButton: 'px-4 py-2 text-xs font-semibold rounded-xl text-[#1b1c1c] border border-[#e5e5e1] transition hover:bg-[#efeded]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
</body>
</html>