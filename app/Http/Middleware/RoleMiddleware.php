<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Log untuk debugging
        Log::info('RoleMiddleware dipanggil', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role ?? 'guest',
            'allowed_roles' => $roles,
            'route' => $request->path(),
        ]);

        // Periksa apakah pengguna memiliki salah satu role yang diizinkan
        if (Auth::check() && in_array(Auth::user()->role, $roles)) {
            return $next($request);
        }

        // Jika tidak sesuai, tampilkan error 403 (Unauthorized)
        abort(403, 'Unauthorized');
    }
}