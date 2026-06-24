<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Redirect staff to administrator portal
            if ($user->isStaff()) {
                return redirect()->route('admin.dashboard');
            }

            // Ensure normal clients have an active pelanggan record
            if (!$user->pelanggan) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'phone' => 'Akun pelanggan Anda tidak valid atau tidak ditemukan.',
                ]);
            }
        }

        return $next($request);
    }
}
