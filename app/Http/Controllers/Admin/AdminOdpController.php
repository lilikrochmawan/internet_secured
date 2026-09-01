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
        $odp = Odp::with(['odcDetail', 'pelanggans.paketDetail', 'parentOdp'])->withCount('pelanggans')->orderBy('id_odp', 'desc')->get();
        $odc = Odc::where('jenis_odc', 'distribusi')->orderBy('nama_odc')->get();
        $allOdps = Odp::orderBy('nama_odp', 'asc')->get();
        return view('admin.odp.index', compact('odp', 'odc', 'allOdps'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_odp' => 'required|string|max:255',
            'port_odp' => 'required|string|max:30',
            'location' => 'required|string',
            'redaman' => 'nullable|string|max:50',
            'has_ratio' => 'nullable|boolean',
            'parent_odp_id' => 'nullable|integer|exists:tbl_odp,id_odp',
        ]);

        $hasRatio = $request->has('has_ratio') && $request->has_ratio;
        $odcId = $request->odc;

        if ($hasRatio) {
            $request->validate(['parent_odp_id' => 'required|integer|exists:tbl_odp,id_odp']);
            $parentOdp = Odp::findOrFail($request->parent_odp_id);
            $odcId = $parentOdp->odc;
        } else {
            $request->validate(['odc' => 'required|integer|exists:tbl_odc,id_odc']);
            $odcCheck = Odc::where('id_odc', $odcId)->first();
            if (!$odcCheck || $odcCheck->jenis_odc !== 'distribusi') {
                return back()->withErrors(['odc' => 'ODP hanya dapat dihubungkan ke ODC jenis Distribusi.'])->withInput();
            }
        }

        Odp::create([
            'nama_odp' => $request->nama_odp,
            'port_odp' => $request->port_odp,
            'location' => $request->location,
            'odc' => $odcId,
            'redaman' => $request->redaman,
            'has_ratio' => $hasRatio ? 1 : 0,
            'parent_odp_id' => $hasRatio ? $request->parent_odp_id : null,
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
            'redaman' => 'nullable|string|max:50',
            'has_ratio' => 'nullable|boolean',
            'parent_odp_id' => 'nullable|integer|exists:tbl_odp,id_odp',
        ]);

        $odp = Odp::findOrFail($request->id_odp);
        
        $hasRatio = $request->has('has_ratio') && $request->has_ratio;
        $odcId = $request->odc;

        if ($hasRatio) {
            $request->validate(['parent_odp_id' => 'required|integer|exists:tbl_odp,id_odp']);
            // Prevent circular dependency (a simple check that parent is not itself)
            if ($request->parent_odp_id == $odp->id_odp) {
                return back()->withErrors(['parent_odp_id' => 'ODP tidak dapat menjadi induk bagi dirinya sendiri.'])->withInput();
            }
            $parentOdp = Odp::findOrFail($request->parent_odp_id);
            $odcId = $parentOdp->odc;
        } else {
            $request->validate(['odc' => 'required|integer|exists:tbl_odc,id_odc']);
            $odcCheck = Odc::where('id_odc', $odcId)->first();
            if (!$odcCheck || $odcCheck->jenis_odc !== 'distribusi') {
                return back()->withErrors(['odc' => 'ODP hanya dapat dihubungkan ke ODC jenis Distribusi.'])->withInput();
            }
        }

        $odp->update([
            'nama_odp' => $request->nama_odp,
            'port_odp' => $request->port_odp,
            'location' => $request->location,
            'odc' => $odcId,
            'redaman' => $request->redaman,
            'has_ratio' => $hasRatio ? 1 : 0,
            'parent_odp_id' => $hasRatio ? $request->parent_odp_id : null,
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
        $odps = Odp::with(['odcDetail', 'pelanggans', 'parentOdp'])->get();
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
                        'has_ratio' => $row->has_ratio,
                        'parent_odp_id' => $row->parent_odp_id,
                        'parent_odp_name' => $row->parentOdp->nama_odp ?? null,
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
