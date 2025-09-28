<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh', 'logout']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);
        $token = Auth::attempt($credentials);

        if (! $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['farmer', 'worker', 'driver'])],
            'phone_number' => 'nullable|numeric|digits_between:10,15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'massage' => 'Semua Kolom Wajib diisi',
                'data' => $validator->errors(),
            ], 401);
        } else {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
                'phone_number' => $request->input('phone_number'),
            ]);
    
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'massage' => 'Register gagal',
                ], 401);
            } else {
                $credentials = $request->only('email', 'password');
                $token = Auth::attempt($credentials);
        
                if (!$token) {
                    return response()->json([
                        'massage' => 'Unauthorized',
                    ], 401);
                }
        
                return $this->respondWithToken($token);
            }
        }
    }

     /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            // Dapatkan token dari request (biasanya dari header Authorization)
            $token = JWTAuth::parseToken()->refresh(); // Ini yang penting!

            // Set token yang baru di instance JWTAuth
            JWTAuth::setToken($token);

            // Ambil payload token baru untuk mendapatkan waktu kedaluwarsa
            $expiration = JWTAuth::getPayload()->get('exp');

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui!',
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expiration - time(), // Waktu kedaluwarsa dalam detik
            ]);
        } catch (TokenExpiredException $e) {
            // Ini seharusnya tidak terjadi jika refresh_ttl masih valid,
            // tetapi bisa terjadi jika token sudah melewati refresh_ttl juga
            return response()->json([
                'success' => false,
                'message' => 'Token sudah sepenuhnya kadaluarsa dan tidak bisa diperbarui.',
                'error' => $e->getMessage()
            ], 401);
        } catch (TokenInvalidException $e) {
            // Token tidak valid (misal, dimodifikasi)
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
                'error' => $e->getMessage()
            ], 401);
        } catch (JWTException $e) {
            // Kesalahan umum JWT (misal, token tidak ada di request)
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui token.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => JWTAuth::factory()->getTTL() * 60 * 72
        ]);
    }
}