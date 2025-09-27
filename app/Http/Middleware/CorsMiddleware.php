<?php

namespace App\Http\Middleware;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);

        // Gunakan metode yang sesuai untuk menambahkan header pada semua jenis response
        foreach ($headers as $key => $value) {
            if (method_exists($response, 'header')) {
                $response->header($key, $value);
            } elseif ($response instanceof BinaryFileResponse) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}