<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-lg font-bold text-[#1b1c1c]">Verifikasi 2-Langkah (2FA)</h2>
        <p class="text-xs text-[#524439] mt-1">
            Kode verifikasi 6-digit (OTP) telah dikirimkan ke email Anda: 
            <strong class="text-[#885215]">{{ Auth::user()->email ?? '' }}</strong>
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-xs text-[#885215] font-semibold" :status="session('status')" />

    <form method="POST" action="{{ route('two-factor.store') }}" class="space-y-4">
        @csrf

        <!-- Kode OTP -->
        <div>
            <x-input-label for="two_factor_code" value="Kode OTP (6-Digit)" class="text-xs font-semibold uppercase text-[#885215]" />
            <x-text-input 
                id="two_factor_code" 
                class="block mt-1 w-full text-center tracking-[0.5em] font-mono text-xl py-3 border-[#847467] focus:border-[#885215]" 
                type="text" 
                name="two_factor_code" 
                maxlength="6"
                placeholder="123456" 
                required 
                autofocus 
                autocomplete="one-time-code" 
            />
            <x-input-error :messages="$errors->get('two_factor_code')" class="mt-2 text-xs" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-[#885215] hover:bg-[#a3692a] text-[#ffffff] font-bold rounded-xl text-sm transition shadow-sm">
                Verifikasi & Masuk
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 pt-4 border-t border-[#e5e5e1] flex items-center justify-between text-xs">
        <span class="text-[#524439]">Belum menerima kode?</span>
        <a href="{{ route('two-factor.resend') }}" class="font-semibold text-[#885215] hover:text-[#a3692a] underline">
            Kirim Ulang Kode OTP
        </a>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-xs text-[#5f5e5e] hover:text-[#1b1c1c] underline">
            Batal & Keluar (Logout)
        </button>
    </form>
</x-guest-layout>
