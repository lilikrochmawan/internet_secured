<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Odp;
use App\Models\Odc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOdpController extends Controller
{
    public function index(Request $request)
    {
        $odp = Odp::with(['odcDetail', 'pelanggans.paketDetail'])->withCount('pelanggans')->orderBy('id_odp', 'desc')->get();
        $odc = Odc::orderBy('nama_odc')->get();
        return view('admin.odp.index', compact('odp', 'odc'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_odp' => 'required|string|max:255',
            'port_odp' => 'required|string|max:30',
            'location' => 'required|string',
            'odc' => 'required|integer|exists:tbl_odc,id_odc',
            'redaman' => 'nullable|string|max:50',
        ]);

        Odp::create([
            'nama_odp' => $request->nama_odp,
            'port_odp' => $request->port_odp,
            'location' => $request->location,
            'odc' => $request->odc,
            'redaman' => $request->redaman,
        ]);

        return redirect()->route('admin.odp.index')->with('success', 'ODP baru berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_odp' => 'required|integer|exists:tbl_odp,id_odp',
            'nama_odp' => 'required|string|max:255',
            'port_odp' => 'required|string|max:30',
            'location' => 'required|string',
            'odc' => 'required|integer|exists:tbl_odc,id_odc',
            'redaman' => 'nullable|string|max:50',
        ]);

        $odp = Odp::findOrFail($request->id_odp);
        $odp->update([
            'nama_odp' => $request->nama_odp,
            'port_odp' => $request->port_odp,
            'location' => $request->location,
            'odc' => $request->odc,
            'redaman' => $request->redaman,
        ]);

        return redirect()->route('admin.odp.index')->with('success', 'ODP berhasil diperbarui!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_odp' => 'required|integer|exists:tbl_odp,id_odp',
        ]);

        // Cek jika ODP masih memiliki pelanggan
        $clientCount = DB::table('tb_pelanggan')->where('odp', $request->id_odp)->count();
        if ($clientCount > 0) {
            return back()->withErrors(['error' => 'Gagal menghapus ODP. Masih terdapat ' . $clientCount . ' pelanggan yang terhubung ke ODP ini.']);
        }

        $odp = Odp::findOrFail($request->id_odp);
        $odp->delete();

        return redirect()->route('admin.odp.index')->with('success', 'ODP berhasil dihapus!');
    }

    public function getCoordinates()
    {
        $odps = Odp::with(['odcDetail', 'pelanggans'])->get();
        $coordinates = [];

        foreach ($odps as $row) {
            if (!empty($row->location)) {
                $coord_parts = explode(',', $row->location);
                if (count($coord_parts) == 2) {
                    $clientsList = [];
                    foreach ($row->pelanggans as $p) {
                        $clientsList[] = [
                            'nama' => $p->nama_pelanggan,
                            'kode' => $p->kode_pelanggan,
                        ];
                    }

                    $coordinates[] = [
                        'id_odp' => $row->id_odp,
                        'nama_odp' => $row->nama_odp,
                        'port_odp' => $row->port_odp,
                        'redaman' => $row->redaman ?? '-',
                        'nama_odc' => $row->odcDetail->nama_odc ?? 'N/A',
                        'clients' => $clientsList,
                        'lat' => floatval(trim($coord_parts[0])),
                        'lng' => floatval(trim($coord_parts[1]))
                    ];
                }
            }
        }

        return response()->json($coordinates);
    }
}
