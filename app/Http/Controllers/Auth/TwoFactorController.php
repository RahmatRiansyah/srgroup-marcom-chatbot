<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\SendTwoFactorCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    /**
     * Tampilkan halaman verifikasi OTP 2FA.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Jika user sudah tidak punya code aktif (artinya sudah terverifikasi), lempar ke dashboard
        if (empty($user->two_factor_code)) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor');
    }

    /**
     * Proses verifikasi kode OTP 2FA yang diinput user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'two_factor_code' => ['required', 'string', 'size:6'],
        ], [
            'two_factor_code.required' => 'Kode OTP wajib diisi.',
            'two_factor_code.size' => 'Kode OTP harus terdiri dari 6 digit angka.',
        ]);

        $user = Auth::user();

        if (!$user || !$user->isTwoFactorCodeValid($request->two_factor_code)) {
            return back()->withErrors([
                'two_factor_code' => 'Kode OTP tidak valid atau sudah kadaluwarsa. Silakan coba lagi.',
            ]);
        }

        // Reset kode di DB dan tandai sesi 2FA terverifikasi
        $user->resetTwoFactorCode();
        $request->session()->put('two_factor_authenticated', true);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Kirim ulang kode OTP 2FA baru ke email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $code = $user->generateTwoFactorCode();
            try {
                $user->notify(new SendTwoFactorCode($code));
            } catch (\Exception $e) {
                // Log error if mail fails, still keep working
                \Log::warning('Email OTP delivery failed: ' . $e->getMessage());
            }

            return back()->with('status', 'Kode OTP baru telah dikirimkan ke email Anda.');
        }

        return redirect()->route('login');
    }
}
