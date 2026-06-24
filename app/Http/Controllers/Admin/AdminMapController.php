<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Odc;
use App\Models\Odp;
use Illuminate\Http\Request;

class AdminMapController extends Controller
{
    public function index(Request $request)
    {
        $totalOdc = Odc::whereNotNull('location')->where('location', '<>', '')->count();
        $totalOdp = Odp::whereNotNull('location')->where('location', '<>', '')->count();
        $totalPelanggan = Pelanggan::whereNotNull('location')->where('location', '<>', '')->count();

        return view('admin.mapping.index', compact('totalOdc', 'totalOdp', 'totalPelanggan'));
    }

    public function getCoordinates()
    {
        $pelanggan = Pelanggan::with('odpDetail')->whereNotNull('location')->where('location', '<>', '')->get();
        $coordinates = [];

        foreach ($pelanggan as $row) {
            $coord_parts = explode(',', $row->location);
            if (count($coord_parts) == 2) {
                $coordinates[] = [
                    'id_pelanggan' => $row->id_pelanggan,
                    'nama_pelanggan' => $row->nama_pelanggan,
                    'kode_pelanggan' => $row->kode_pelanggan,
                    'alamat' => $row->alamat,
                    'no_telp' => $row->no_telp,
                    'odp_name' => $row->odpDetail->nama_odp ?? 'N/A',
                    'lat' => floatval(trim($coord_parts[0])),
                    'lng' => floatval(trim($coord_parts[1]))
                ];
            }
        }

        return response()->json($coordinates);
    }
}
