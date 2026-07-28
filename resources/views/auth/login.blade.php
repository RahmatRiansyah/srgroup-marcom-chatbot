<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Masuk - Holliday Group</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-black.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-[#F8F7F4] text-[#18181B] antialiased min-h-screen flex flex-col justify-center items-center px-4 py-12 selection:bg-[#B86B1B] selection:text-white">

    <div class="w-full max-w-md mx-auto">
        
        <!-- SATU-SATUNYA KARTU FORM (TUNGGAL) -->
        <div class="bg-white p-8 rounded-2xl border border-[#EBEAE5] shadow-xs">
            
            <!-- SATU-SATUNYA LOGO (DI DALAM KARTU) -->
            <div class="text-center mb-6">
                <a href="{{ url('/') }}" class="inline-block mb-3">
                    <img src="{{ asset('images/srgroup-logo-black.svg') }}" 
                         alt="Holliday Group Logo" 
                         class="h-16 w-auto mx-auto object-contain"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-[#18181B] font-extrabold text-2xl tracking-widest\'>HOLLIDAY GROUP</span>';">
                </a>
                <h2 class="text-xl font-extrabold text-[#18181B] tracking-tight">
                    Selamat Datang Kembali
                </h2>
                <p class="mt-1 text-xs text-[#71717A]">
                    Silakan masuk untuk mengakses portal internal Holliday Group
                </p>
            </div>

            <!-- Session Status (Pesan Berhasil/Gagal Laravel) -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="!text-xs !font-bold !text-[#18181B] uppercase tracking-wider mb-1.5" />
                    <x-text-input id="email" 
                        class="block w-full rounded-xl border-[#EBEAE5] bg-[#F8F7F4]/60 focus:bg-white focus:border-[#B86B1B] focus:ring-[#B86B1B] text-sm text-[#18181B] py-2.5 px-3.5" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autofocus 
                        autocomplete="username" 
                        placeholder="nama@perusahaan.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-600" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <x-input-label for="password" :value="__('Password')" class="!text-xs !font-bold !text-[#18181B] uppercase tracking-wider" />
                        @if (Route::has('password.request'))
                            <a class="text-xs font-semibold text-[#B86B1B] hover:text-[#965410] hover:underline" href="{{ route('password.request') }}">
                                {{ __('Lupa kata sandi?') }}
                            </a>
                        @endif
                    </div>

                    <x-text-input id="password" 
                        class="block w-full rounded-xl border-[#EBEAE5] bg-[#F8F7F4]/60 focus:bg-white focus:border-[#B86B1B] focus:ring-[#B86B1B] text-sm text-[#18181B] py-2.5 px-3.5"
                        type="password"
                        name="password"
                        required 
                        autocomplete="current-password" 
                        placeholder="••••••••" />

                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-600" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-[#EBEAE5] text-[#B86B1B] focus:ring-[#B86B1B] focus:ring-offset-1" name="remember">
                        <span class="ms-2.5 text-xs font-medium text-[#71717A]">{{ __('Ingat saya di perangkat ini') }}</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#18181B] hover:bg-[#27272A] text-white text-sm font-semibold tracking-wide shadow-md transition-colors">
                        <span>{{ __('Masuk ke Akun') }}</span>
                        <svg class="w-4 h-4 text-[#B86B1B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Link Kembali -->
        <div class="mt-6 text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-xs text-[#71717A] hover:text-[#18181B] font-medium transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Halaman Utama</span>
            </a>
        </div>

    </div>

</body>
</html>