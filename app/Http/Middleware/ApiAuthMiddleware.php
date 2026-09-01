<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau tidak disertakan.'
            ], 401);
        }

        $token = substr($header, 7);

        // Cari user berdasarkan api_token
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid atau telah berakhir. Silakan login kembali.'
            ], 401);
        }

        // Daftarkan user ke sistem Auth agar Auth::user() bekerja
        Auth::login($user);

        // Cek jika normal user memiliki record pelanggan yang valid
        if (!$user->isStaff() && !$user->pelanggan) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Akun pelanggan Anda tidak valid atau tidak ditemukan.'
            ], 403);
        }

        return $next($request);
    }
}
