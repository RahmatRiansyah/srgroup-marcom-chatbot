<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            // Jika user memiliki two_factor_code aktif (belum terverifikasi)
            if (!empty($user->two_factor_code)) {
                if (!$request->routeIs('two-factor.*') && !$request->routeIs('logout')) {
                    return redirect()->route('two-factor.index');
                }
            }
        }

        return $next($request);
    }
}
