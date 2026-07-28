<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Verifikasi Email - Holliday Group</title>

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
        
        <!-- KARTU TUNGGAL VERIFIKASI EMAIL -->
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
                    Verifikasi Alamat Email
                </h2>
                <p class="mt-2 text-xs text-[#71717A] leading-relaxed">
                    {{ __('Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan. Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan ulang.') }}
                </p>
            </div>

            <!-- Session Status Alert (Notifikasi Email Terkirim) -->
            @if (session('status') == 'verification-link-sent')
                <div class="mb-5 p-3.5 rounded-xl bg-[#F8F7F4] border border-[#B86B1B]/30 text-xs font-semibold text-[#B86B1B] text-center leading-relaxed">
                    {{ __('Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda berikan saat pendaftaran.') }}
                </div>
            @endif

            <!-- Form Kirim Ulang Verifikasi Email -->
            <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                @csrf

                <div>
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#18181B] hover:bg-[#27272A] text-white text-sm font-semibold tracking-wide shadow-md transition-colors">
                        <span>{{ __('Kirim Ulang Email Verifikasi') }}</span>
                        <svg class="w-4 h-4 text-[#B86B1B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </form>

            <!-- Form Logout / Keluar -->
            <div class="mt-6 pt-5 border-t border-[#EBEAE5] text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-[#71717A] hover:text-[#18181B] font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>{{ __('Keluar (Log Out)') }}</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

</body>
</html>