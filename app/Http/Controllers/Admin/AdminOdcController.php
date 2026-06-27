<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Odc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOdcController extends Controller
{
    public function index(Request $request)
    {
        $odc = Odc::with(['odps.pelanggans', 'parentOdc'])->withCount('odps')->orderBy('id_odc', 'desc')->get();
        $mainOdcs = Odc::where('jenis_odc', 'utama')->orderBy('nama_odc', 'asc')->get();
        return view('admin.odc.index', compact('odc', 'mainOdcs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_odc' => 'required|string|max:255',
            'perangkat_odc' => 'required|string|max:50',
            'port_odc' => 'required|string|max:30',
            'location' => 'required|string',
            'redaman' => 'nullable|string|max:50',
            'tube' => 'nullable|string|max:50',
            'core_number' => 'nullable|integer',
            'jenis_odc' => 'required|string|in:utama,distribusi',
            'parent_id' => 'nullable|integer|exists:tbl_odc,id_odc',
        ]);

        Odc::create([
            'nama_odc' => $request->nama_odc,
            'perangkat_odc' => $request->perangkat_odc,
            'port_odc' => $request->port_odc,
            'location' => $request->location,
            'redaman' => $request->redaman,
            'tube' => $request->tube,
            'core_number' => $request->core_number,
            'jenis_odc' => $request->jenis_odc,
            'parent_id' => $request->jenis_odc === 'distribusi' ? $request->parent_id : null,
        ]);

        return redirect()->route('admin.odc.index')->with('success', 'ODC baru berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_odc' => 'required|integer|exists:tbl_odc,id_odc',
            'nama_odc' => 'required|string|max:255',
            'perangkat_odc' => 'required|string|max:50',
            'port_odc' => 'required|string|max:30',
            'location' => 'required|string',
            'redaman' => 'nullable|string|max:50',
            'tube' => 'nullable|string|max:50',
            'core_number' => 'nullable|integer',
            'jenis_odc' => 'required|string|in:utama,distribusi',
            'parent_id' => 'nullable|integer|exists:tbl_odc,id_odc',
        ]);

        $odc = Odc::findOrFail($request->id_odc);
        $odc->update([
            'nama_odc' => $request->nama_odc,
            'perangkat_odc' => $request->perangkat_odc,
            'port_odc' => $request->port_odc,
            'location' => $request->location,
            'redaman' => $request->redaman,
            'tube' => $request->tube,
            'core_number' => $request->core_number,
            'jenis_odc' => $request->jenis_odc,
            'parent_id' => $request->jenis_odc === 'distribusi' ? $request->parent_id : null,
        ]);

        return redirect()->route('admin.odc.index')->with('success', 'ODC berhasil diperbarui!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_odc' => 'required|integer|exists:tbl_odc,id_odc',
        ]);

        // Cek jika ODC masih memiliki ODP
        $odpCount = DB::table('tbl_odp')->where('odc', $request->id_odc)->count();
        if ($odpCount > 0) {
            return back()->withErrors(['error' => 'Gagal menghapus ODC. Masih terdapat ' . $odpCount . ' ODP yang terhubung ke ODC ini.']);
        }

        $odc = Odc::findOrFail($request->id_odc);
        $odc->delete();

        return redirect()->route('admin.odc.index')->with('success', 'ODC berhasil dihapus!');
    }

    public function getCoordinates()
    {
        $odcs = Odc::all();
        $coordinates = [];

        foreach ($odcs as $row) {
            if (!empty($row->location)) {
                $coord_parts = explode(',', $row->location);
                if (count($coord_parts) == 2) {
                    $coordinates[] = [
                        'id_odc' => $row->id_odc,
                        'nama_odc' => $row->nama_odc,
                        'port_odc' => $row->port_odc,
                        'perangkat_odc' => $row->perangkat_odc,
                        'redaman' => $row->redaman ?? '-',
                        'tube' => $row->tube ?? '-',
                        'core_number' => $row->core_number ?? '-',
                        'lat' => floatval(trim($coord_parts[0])),
                        'lng' => floatval(trim($coord_parts[1])),
                        'jenis_odc' => $row->jenis_odc,
                        'parent_id' => $row->parent_id,
                    ];
                }
            }
        }

        return response()->json($coordinates);
    }
}
