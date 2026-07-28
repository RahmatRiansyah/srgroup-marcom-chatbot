<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Lupa Kata Sandi - Holliday Group</title>

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
        
        <!-- KARTU TUNGGAL LUPA PASSWORD -->
        <div class="bg-white p-8 rounded-2xl border border-[#EBEAE5] shadow-xs">
            
            <!-- Logo & Header -->
            <div class="text-center mb-6">
                <a href="{{ url('/') }}" class="inline-block mb-3">
                    <img src="{{ asset('images/srgroup-logo-black.svg') }}" 
                         alt="Holliday Group Logo" 
                         class="h-16 w-auto mx-auto object-contain"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-[#18181B] font-extrabold text-2xl tracking-widest\'>HOLLIDAY GROUP</span>';">
                </a>
                <h2 class="text-xl font-extrabold text-[#18181B] tracking-tight">
                    Lupa Kata Sandi?
                </h2>
                <p class="mt-2 text-xs text-[#71717A] leading-relaxed">
                    Tidak masalah. Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
                </p>
            </div>

            <!-- Session Status (Notifikasi jika email berhasil dikirim) -->
            <x-auth-session-status class="mb-4 text-xs font-semibold text-[#B86B1B]" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#18181B] hover:bg-[#27272A] text-white text-sm font-semibold tracking-wide shadow-md transition-colors">
                        <span>{{ __('Kirim Tautan Reset Password') }}</span>
                        <svg class="w-4 h-4 text-[#B86B1B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Link Kembali ke Halaman Login -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs text-[#71717A] hover:text-[#18181B] font-medium transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>

    </div>

</body>
</html>