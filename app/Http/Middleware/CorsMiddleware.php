<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $allowedOrigins = [
            'https://goagrolink.com',
            'http://localhost:5173',
            'http://127.0.0.1:5173',
        ];

        $origin = $request->header('Origin');

        // Periksa apakah origin diizinkan
        if (in_array($origin, $allowedOrigins)) {
            $headers = [
                'Access-Control-Allow-Origin'      => $origin,
                'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age'           => '86400',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
            ];

            // 1. Tangani request pre-flight (OPTIONS)
            if ($request->isMethod('OPTIONS')) {
                return response()->json('{"method":"OPTIONS"}', 200, $headers);
            }

            // 2. Lanjutkan request ke controller
            $response = $next($request);

            // 3. Tambahkan headers ke response dari controller
            foreach ($headers as $key => $value) {
                // Gunakan setHeader untuk kompatibilitas lebih baik
                $response->headers->set($key, $value);
            }

            return $response;
        }

        // Jika origin tidak diizinkan, biarkan request tetap berjalan tanpa header CORS
        return $next($request);
    }
}