<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Roadmap Minggu 7: "Kelola user yang boleh akses chatbot". Kalau admin
 * menonaktifkan sebuah akun (User::is_active = false) lewat panel
 * /admin/users, middleware ini yang menegakkannya: user itu langsung
 * di-logout paksa dan diarahkan balik ke halaman login dengan pesan jelas,
 * daripada cuma dibiarkan lihat halaman kosong/error mentah.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan oleh admin. Hubungi admin kalau ini keliru.',
            ]);
        }

        return $next($request);
    }
}
