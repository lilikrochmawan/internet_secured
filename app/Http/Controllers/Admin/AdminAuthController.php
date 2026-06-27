<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login dan memiliki level staff, langsung arahkan ke dashboard admin
        if (Auth::check() && in_array(Auth::user()->level, ['admin', 'kasir', 'teknisi', 'sales', 'mitra', 'noc'])) {
            return redirect()->route('admin.dashboard');
        }

        $profile = \Illuminate\Support\Facades\DB::table('tb_profile')->first();
        return view('admin.auth.login', compact('profile'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cari user berdasarkan username di tb_user
        $user = User::where('username', $request->username)->first();

        // Verifikasi keberadaan user dan kecocokan password secara aman
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Username atau Password salah.',
            ])->withInput($request->only('username'));
        }

        // Cek apakah user memiliki hak akses administrator
        if (!in_array($user->level, ['admin', 'kasir', 'teknisi', 'sales', 'mitra', 'noc'])) {
            return back()->withErrors([
                'username' => 'Anda tidak memiliki hak akses administrator.',
            ])->withInput($request->only('username'));
        }

        // Log in user ke session
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Anda berhasil keluar.');
    }
}
