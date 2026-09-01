<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        // Bersihkan nomor telepon - hanya sisakan angka
        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        // Cari pelanggan berdasarkan nomor telepon
        $pelanggan = Pelanggan::findByPhone($phone);

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP tidak ditemukan dalam sistem.'
            ], 404);
        }

        // Cari user yang diasosiasikan dengan pelanggan ini
        $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna tidak ditemukan untuk pelanggan ini.'
            ], 404);
        }

        // Generate token baru
        $token = Str::random(60);
        $user->api_token = $token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama_user' => $user->nama_user,
                'level' => $user->level,
                'foto' => $user->foto,
            ],
            'pelanggan' => [
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'kode_pelanggan' => $pelanggan->kode_pelanggan,
                'nama_pelanggan' => $pelanggan->nama_pelanggan,
                'no_telp' => $pelanggan->no_telp,
                'alamat' => $pelanggan->alamat,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;
        
        if ($pelanggan) {
            $pelanggan->load('paketDetail');
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'pelanggan' => $pelanggan
        ]);
    }
}
