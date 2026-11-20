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
        $odps = Odp::withCount('pelanggans')->orderBy('nama_odp', 'asc')->get();
        $allOdcs = Odc::orderBy('nama_odc', 'asc')->get();

        return view('admin.mapping.index', compact('totalOdc', 'totalOdp', 'totalPelanggan', 'odps', 'allOdcs'));
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
                    'id_odp' => $row->odp,
                    'odp_name' => $row->odpDetail->nama_odp ?? 'N/A',
                    'lat' => floatval(trim($coord_parts[0])),
                    'lng' => floatval(trim($coord_parts[1]))
                ];
            }
        }

        return response()->json($coordinates);
    }

    public function updateClientOdp(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|integer',
            'id_odp' => 'required|integer',
        ]);

        $pelanggan = Pelanggan::findOrFail($request->id_pelanggan);
        $pelanggan->odp = $request->id_odp;
        $pelanggan->save();

        return response()->json([
            'success' => true,
            'message' => 'Koneksi ODP berhasil diperbarui',
            'new_odp_name' => $pelanggan->odpDetail->nama_odp ?? 'N/A'
        ]);
    }

    public function updateOdpOdc(Request $request)
    {
        $request->validate([
            'id_odp' => 'required|integer|exists:tbl_odp,id_odp',
            'id_odc' => 'required|integer|exists:tbl_odc,id_odc',
        ]);

        $odp = Odp::findOrFail($request->id_odp);
        $odc = Odc::findOrFail($request->id_odc);

        $odp->odc = $request->id_odc;
        $odp->has_ratio = 0;
        $odp->parent_odp_id = null;
        $odp->save();

        return response()->json([
            'success' => true,
            'message' => 'Koneksi ODP ke ODC ' . $odc->nama_odc . ' berhasil disimpan!',
            'id_odp' => $odp->id_odp,
            'id_odc' => $odc->id_odc,
            'new_odc_name' => $odc->nama_odc,
        ]);
    }

    public function updateOdcParent(Request $request)
    {
        $request->validate([
            'id_odc' => 'required|integer|exists:tbl_odc,id_odc',
            'parent_id' => 'nullable|integer|exists:tbl_odc,id_odc',
        ]);

        if ($request->filled('parent_id') && $request->parent_id == $request->id_odc) {
            return response()->json([
                'success' => false,
                'message' => 'ODC tidak dapat menginduk ke dirinya sendiri.'
            ], 422);
        }

        $odc = Odc::findOrFail($request->id_odc);

        if ($request->filled('parent_id')) {
            $parentOdc = Odc::findOrFail($request->parent_id);
            $odc->parent_id = $request->parent_id;
            $odc->jenis_odc = 'distribusi';
            $odc->save();

            return response()->json([
                'success' => true,
                'message' => 'ODC Induk berhasil dihubungkan ke ' . $parentOdc->nama_odc . '!',
                'id_odc' => $odc->id_odc,
                'parent_id' => $parentOdc->id_odc,
                'parent_nama_odc' => $parentOdc->nama_odc,
                'jenis_odc' => 'distribusi'
            ]);
        } else {
            $odc->parent_id = null;
            $odc->jenis_odc = 'utama';
            $odc->save();

            return response()->json([
                'success' => true,
                'message' => 'ODC berhasil diatur sebagai ODC Utama (Pusat / Tanpa Induk)!',
                'id_odc' => $odc->id_odc,
                'parent_id' => null,
                'parent_nama_odc' => 'Pusat (Tidak menginduk)',
                'jenis_odc' => 'utama'
            ]);
        }
    }
}
