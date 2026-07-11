<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();
        
        // Periksa apakah user memiliki hak akses administrator/staff
        if (!in_array($user->level, ['admin', 'kasir', 'teknisi', 'sales', 'mitra', 'noc'])) {
            Auth::logout();
            return redirect()->route('admin.login')->withErrors([
                'username' => 'Anda tidak memiliki hak akses untuk masuk ke halaman administrator.',
            ]);
        }

        // Map routes or paths to menu keys
        $path = $request->path(); // e.g. "administrator/monitoring/active" or "administrator/monitoring"
        // remove "administrator" prefix
        $subPath = preg_replace('/^administrator\/?/', '', $path);
        
        // Find which menu it belongs to
        $menuKey = null;
        if ($subPath === '' || $subPath === 'dashboard') {
            $menuKey = 'dashboard';
        } elseif (preg_match('/^monitoring/', $subPath)) {
            $menuKey = 'monitoring';
        } elseif (preg_match('/^order-pemasangan/', $subPath)) {
            $menuKey = 'order_pemasangan';
        } elseif (preg_match('/^tr069/', $subPath)) {
            $menuKey = 'tr069';
        } elseif (preg_match('/^odc/', $subPath)) {
            $menuKey = 'odc';
        } elseif (preg_match('/^odp/', $subPath)) {
            $menuKey = 'odp';
        } elseif (preg_match('/^mapping/', $subPath)) {
            $menuKey = 'mapping';
        } elseif (preg_match('/^custom-pesan/', $subPath)) {
            $menuKey = 'custom_pesan';
        } elseif (preg_match('/^broadcast/', $subPath)) {
            $menuKey = 'broadcast';
        } elseif (preg_match('/^pelanggan/', $subPath)) {
            $menuKey = 'pelanggan';
        } elseif (preg_match('/^paket/', $subPath)) {
            $menuKey = 'paket';
        } elseif (preg_match('/^ont/', $subPath)) {
            $menuKey = 'ont';
        } elseif (preg_match('/^transaksi/', $subPath)) {
            $menuKey = 'transaksi';
        } elseif (preg_match('/^kas/', $subPath)) {
            $menuKey = 'kas';
        } elseif (preg_match('/^keluhan/', $subPath)) {
            $menuKey = 'keluhan';
        } elseif (preg_match('/^pengguna/', $subPath)) {
            $menuKey = 'pengguna';
        } elseif (preg_match('/^pengaturan-client/', $subPath) || preg_match('/^branch/', $subPath) || preg_match('/^sub-branch/', $subPath) || preg_match('/^access/', $subPath)) {
            // Pengaturan Client page is admin-only
            if ($user->level !== 'admin') {
                return abort(403, 'Unauthorized action.');
            }
        } elseif (preg_match('/^pengaturan/', $subPath)) {
            // General Pengaturan page is admin-only
            if ($user->level !== 'admin') {
                return abort(403, 'Unauthorized action.');
            }
        } elseif (preg_match('/^logs/', $subPath)) {
            // Log Aktivitas is admin-only
            if ($user->level !== 'admin') {
                return abort(403, 'Unauthorized action.');
            }
        } elseif (preg_match('/^teknisi\/clients/', $subPath)) {
            // Dapatkan akses untuk admin dan teknisi
            if (!in_array($user->level, ['admin', 'teknisi'])) {
                return abort(403, 'Unauthorized action.');
            }
        }

        if ($menuKey && !$user->hasMenuAccess($menuKey)) {
            return abort(403, 'Anda tidak memiliki hak akses untuk menu ini.');
        }

        return $next($request);
    }
}
