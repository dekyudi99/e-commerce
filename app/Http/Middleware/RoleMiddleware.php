<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Accepts multiple roles: 'role:admin,user'
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Jika user tidak terotentikasi, atau
        // jika user terotentikasi TAPI role-nya TIDAK ADA DI DALAM daftar roles yang diizinkan
        if (!$user || !in_array($user->role, $roles)) {
            // Maka, kembalikan response Unauthorized
            return response()->json([
                'message' => 'Unauthorized - Role restricted, Your role is ' . ($user ? $user->role : 'unknown')
            ], 403);
        }

        // Jika user terotentikasi dan role-nya diizinkan, lanjutkan request
        return $next($request);
    }
}