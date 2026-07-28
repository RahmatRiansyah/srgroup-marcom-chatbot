<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Verifikasi 2FA - Holliday Group</title>

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
        
        <!-- KARTU TUNGGAL VERIFIKASI 2FA -->
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
                    Verifikasi 2-Langkah (2FA)
                </h2>
                <p class="mt-1.5 text-xs text-[#71717A] leading-relaxed">
                    Kode verifikasi 6-digit (OTP) telah dikirimkan ke email Anda: <br>
                    <strong class="font-bold text-[#B86B1B]">{{ Auth::user()->email ?? '' }}</strong>
                </p>
            </div>

            <!-- Session Status (Pesan sukses/status bawaan) -->
            <x-auth-session-status class="mb-4 text-xs font-semibold text-[#B86B1B]" :status="session('status')" />

            <!-- Form OTP -->
            <form method="POST" action="{{ route('two-factor.store') }}" class="space-y-5">
                @csrf

                <!-- Kode OTP Input -->
                <div>
                    <x-input-label for="two_factor_code" value="Kode OTP (6-Digit)" class="!text-xs !font-bold !text-[#18181B] uppercase tracking-wider mb-2 text-center" />
                    
                    <x-text-input 
                        id="two_factor_code" 
                        class="block w-full text-center tracking-[0.4em] sm:tracking-[0.5em] font-mono text-2xl font-bold py-3.5 rounded-xl border-[#EBEAE5] bg-[#F8F7F4]/60 focus:bg-white focus:border-[#B86B1B] focus:ring-[#B86B1B] text-[#18181B] transition-all duration-200" 
                        type="text" 
                        name="two_factor_code" 
                        maxlength="6"
                        placeholder="123456" 
                        required 
                        autofocus 
                        autocomplete="one-time-code" 
                    />
                    
                    <x-input-error :messages="$errors->get('two_factor_code')" class="mt-2 text-xs text-rose-600 text-center" />
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl bg-[#18181B] hover:bg-[#27272A] text-white text-sm font-semibold tracking-wide shadow-md transition-colors">
                        <span>Verifikasi & Masuk</span>
                        <svg class="w-4 h-4 text-[#B86B1B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

            <!-- Resend Code Area -->
            <div class="mt-6 pt-5 border-t border-[#EBEAE5] flex items-center justify-between text-xs">
                <span class="text-[#71717A]">Belum menerima kode?</span>
                <a href="{{ route('two-factor.resend') }}" class="font-bold text-[#B86B1B] hover:text-[#965410] hover:underline transition-colors">
                    Kirim Ulang Kode OTP
                </a>
            </div>

        </div>

        <!-- Form Cancel / Logout -->
        <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-[#71717A] hover:text-[#18181B] font-medium transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Batal & Keluar (Logout)</span>
            </button>
        </form>

    </div>

</body>
</html>