<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPaketController extends Controller
{
    public function index()
    {
        $paket = Paket::orderBy('id_paket', 'desc')->get();
        $mikrotiks = DB::table('tbl_mikrotik')->get();
        return view('admin.paket.index', compact('paket', 'mikrotiks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string',
            'harga' => 'required|numeric',
            'id_pmikrotik' => 'nullable|string',
        ]);

        Paket::create([
            'nama_paket' => htmlspecialchars(strip_tags($request->nama_paket)),
            'harga' => $request->harga,
            'ppn' => $request->ppn ?? 0,
            'id_pmikrotik' => $request->id_pmikrotik ?? '',
        ]);

        return redirect()->route('admin.paket.index')->with('success', 'Paket internet berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_paket' => 'required|integer',
            'nama_paket' => 'required|string',
            'harga' => 'required|numeric',
            'id_pmikrotik' => 'nullable|string',
        ]);

        $paket = Paket::findOrFail($request->id_paket);
        $paket->update([
            'nama_paket' => htmlspecialchars(strip_tags($request->nama_paket)),
            'harga' => $request->harga,
            'ppn' => $request->ppn ?? 0,
            'id_pmikrotik' => $request->id_pmikrotik ?? '',
        ]);

        return redirect()->route('admin.paket.index')->with('success', 'Paket internet berhasil diubah!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_paket' => 'required|integer',
        ]);

        $paket = Paket::findOrFail($request->id_paket);
        $paket->delete();

        return redirect()->route('admin.paket.index')->with('success', 'Paket internet berhasil dihapus!');
    }

    public function getMikrotikProfiles(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi router Mikrotik tidak ditemukan.'
            ]);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 5;
        $API->attempts = 1;
        $API->delay = 0;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $profiles = $API->comm("/ppp/profile/print") ?: [];
            $API->disconnect();

            $profileNames = [];
            foreach ($profiles as $profile) {
                if (isset($profile['name'])) {
                    $profileNames[] = $profile['name'];
                }
            }

            return response()->json([
                'success' => true,
                'profiles' => $profileNames
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Gagal terhubung ke router Mikrotik \"" . ($mikrotik->nama_mikrotik ?? 'Router') . "\" (" . $mikrotik->ip . ")."
        ]);
    }
}
