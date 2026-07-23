<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola User - SR Group</title>
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
                    <h1 class="font-bold text-lg text-white">Kelola User</h1>
                    <p class="text-xs text-slate-400">Atur siapa yang jadi admin dan siapa yang boleh akses chatbot</p>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <main class="p-6 max-w-7xl mx-auto w-full space-y-6">

            <!-- Alert Success / Error -->
            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 px-4 py-3 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabel Daftar User -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-700">
                    <h3 class="text-base font-semibold text-white">Daftar User</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-300">
                        <thead class="text-xs uppercase bg-slate-700/50 text-slate-400">
                            <tr>
                                <th class="px-6 py-3.5">Nama</th>
                                <th class="px-6 py-3.5">Email</th>
                                <th class="px-6 py-3.5">Role</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($users as $user)
                                <tr class="hover:bg-slate-700/30 transition">
                                    <td class="px-6 py-4 font-semibold text-white">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="text-[10px] text-slate-500 font-normal">(kamu)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        @if($user->id === auth()->id())
                                            <span class="bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-2.5 py-1 rounded-lg text-xs font-medium">
                                                {{ $user->role }}
                                            </span>
                                        @else
                                            <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="role" onchange="this.form.submit()"
                                                    class="bg-slate-900 border border-slate-700 text-slate-100 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-indigo-500">
                                                    <option value="member" {{ $user->role === 'member' ? 'selected' : '' }}>member</option>
                                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
                                                </select>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->is_active)
                                            <span class="px-2.5 py-1 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-lg">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST"
                                                onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->name }}?');" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                    class="{{ $user->is_active ? 'text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 bg-rose-500/10 border-rose-500/20' : 'text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/20 bg-emerald-500/10 border-emerald-500/20' }} border px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-600 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-slate-500">
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

</body>
</html>
