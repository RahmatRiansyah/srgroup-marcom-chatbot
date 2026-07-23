<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Roadmap Minggu 7: batasi panel admin (data source, log scraping, kelola user)
 * cuma buat user dengan role 'admin'. Dipasang di routes/web.php lewat alias
 * 'admin' (lihat bootstrap/app.php), SELALU dipasang setelah middleware 'auth'.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'Halaman ini khusus untuk admin.');
        }

        return $next($request);
    }
}
