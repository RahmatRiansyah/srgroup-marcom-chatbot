<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola User - SR Group</title>

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
                    <h1 class="font-bold text-lg text-[#1b1c1c]">Kelola User</h1>
                    <p class="text-xs text-[#524439]">Atur siapa yang jadi admin dan siapa yang boleh akses chatbot</p>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <main class="p-6 max-w-7xl mx-auto w-full space-y-6">

            <!-- Alert Success / Error -->
            @if(session('success'))
                <div class="bg-[#f4e9de] border border-[#e7c5a6] text-[#885215] px-4 py-3 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-[#f4e3d6] border border-[#e7c5a6] text-[#885215] px-4 py-3 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Error Validasi (mis. email sudah dipakai, password kurang kuat) -->
            @if($errors->any())
                <div class="bg-[#f4e3d6] border border-[#e7c5a6] text-[#885215] px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Tambah User -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl p-6 shadow-sm shadow-[#0000000d]">
                <h3 class="text-base font-semibold text-[#1b1c1c] mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#885215]"></span>
                    Tambah User Baru
                </h3>

                <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    @csrf
                    <div class="lg:col-span-1">
                        <label class="block text-xs font-medium text-[#524439] mb-2">Nama</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Nama lengkap"
                            required
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                    </div>

                    <div class="lg:col-span-1">
                        <label class="block text-xs font-medium text-[#524439] mb-2">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            required
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                    </div>

                    <div class="lg:col-span-1">
                        <label class="block text-xs font-medium text-[#524439] mb-2">Password</label>
                        <input
                            type="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            required
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                    </div>

                    <div class="lg:col-span-1">
                        <label class="block text-xs font-medium text-[#524439] mb-2">Konfirmasi Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Ulangi password"
                            required
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                    </div>

                    <div class="lg:col-span-1">
                        <label class="block text-xs font-medium text-[#524439] mb-2">Role</label>
                        <select
                            name="role"
                            required
                            class="w-full bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#885215] transition"
                        >
                            <option value="member" selected>member</option>
                            <option value="admin">admin</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 lg:col-span-5 flex justify-end">
                        <button
                            type="submit"
                            class="bg-[#885215] hover:bg-[#784a15] text-[#ffffff] font-medium px-5 py-2.5 rounded-xl transition text-sm shadow-sm shadow-[#885215]/20"
                        >
                            Simpan User
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar User -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl overflow-hidden shadow-sm shadow-[#0000000d]">
                <div class="p-4 border-b border-[#e5e5e1]">
                    <h3 class="text-base font-semibold text-[#1b1c1c]">Daftar User</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#524439]">
                        <thead class="text-xs uppercase bg-[#f5f3f3] text-[#524439]">
                            <tr>
                                <th class="px-6 py-3.5">Nama</th>
                                <th class="px-6 py-3.5">Email</th>
                                <th class="px-6 py-3.5">Role</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e5e1]">
                            @forelse($users as $user)
                                <tr class="hover:bg-[#fbf9f8] transition">
                                    <td class="px-6 py-4 font-semibold text-[#1b1c1c]">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="text-[10px] text-[#847467] font-normal">(kamu)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        @if($user->id === auth()->id())
                                            <span class="bg-[#885215]/10 text-[#885215] border border-[#885215]/20 px-2.5 py-1 rounded-lg text-xs font-medium">
                                                {{ $user->role }}
                                            </span>
                                        @else
                                            <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="role" onchange="this.form.submit()"
                                                    class="bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-[#885215]">
                                                    <option value="member" {{ $user->role === 'member' ? 'selected' : '' }}>member</option>
                                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
                                                </select>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->is_active)
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#2d6d3a] bg-[#d7f1dd] border border-[#b4e5c2] rounded-lg">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium text-[#885215] bg-[#f4e3d6] border border-[#e7c5a6] rounded-lg">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($user->id !== auth()->id())
                                            <form id="form-toggle-{{ $user->id }}" action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                
                                                @if($user->is_active)
                                                    <!-- Jika Aktif, tampilkan tombol Nonaktifkan yang memanggil SweetAlert2 -->
                                                    <button type="button"
                                                        onclick="konfirmasiNonaktifkan('{{ $user->name }}', 'form-toggle-{{ $user->id }}')"
                                                        class="text-[#885215] hover:text-[#1b1c1c] hover:bg-[#f4e3d6] bg-[#f4e3d6] border-[#e7c5a6] border px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                        Nonaktifkan
                                                    </button>
                                                @else
                                                    <!-- Jika Nonaktif, tombol untuk Mengaktifkan kembali -->
                                                    <button type="submit"
                                                        class="text-[#8b8f94] hover:text-[#1b1c1c] hover:bg-[#f0f0f0] bg-[#f0f0f0] border-[#d9d9d9] border px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                        Aktifkan
                                                    </button>
                                                @endif
                                            </form>
                                        @else
                                            <span class="text-[#885215] text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-[#847467]">
                                        Belum ada user.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Script Global SweetAlert2 untuk Konfirmasi Nonaktifkan -->
    <script>
        function konfirmasiNonaktifkan(namaAkun, formIdOrUrl) {
            let targetNama = namaAkun.trim();
            if (targetNama.toLowerCase().startsWith('akun ')) {
                targetNama = targetNama.substring(5).trim();
            }

            Swal.fire({
                title: 'Konfirmasi Nonaktifkan',
                html: `Apakah Anda yakin ingin menonaktifkan akun <strong>"${targetNama}"</strong>?`,
                icon: 'warning',
                iconColor: '#885215',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#f5f3f3',
                confirmButtonText: 'Ya, Nonaktifkan',
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
                    if (typeof formIdOrUrl === 'string' && document.getElementById(formIdOrUrl)) {
                        document.getElementById(formIdOrUrl).submit();
                    } else if (typeof formIdOrUrl === 'function') {
                        formIdOrUrl();
                    }
                }
            });
        }
    </script>
</body>
</html>