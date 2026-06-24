<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perangkat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOntController extends Controller
{
    public function index()
    {
        $ontDevices = Perangkat::with(['pelanggan'])->orderBy('id_perangkat', 'desc')->get();
        return view('admin.ont.index', compact('ontDevices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_perangkat' => 'required|string|max:255',
        ]);

        Perangkat::create([
            'nama_perangkat' => htmlspecialchars(strip_tags($request->nama_perangkat)),
        ]);

        return redirect()->route('admin.ont.index')->with('success', 'Data ONT baru berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_perangkat' => 'required|integer',
            'nama_perangkat' => 'required|string|max:255',
        ]);

        $perangkat = Perangkat::findOrFail($request->id_perangkat);
        $perangkat->update([
            'nama_perangkat' => htmlspecialchars(strip_tags($request->nama_perangkat)),
        ]);

        return redirect()->route('admin.ont.index')->with('success', 'Data ONT berhasil diperbarui!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_perangkat' => 'required|integer',
        ]);

        // Cek apakah perangkat ini sedang digunakan oleh pelanggan
        $isInUse = DB::table('tb_pelanggan')->where('id_perangkat', $request->id_perangkat)->exists();
        if ($isInUse) {
            return back()->withErrors(['error' => 'Data ONT ini tidak bisa dihapus karena masih digunakan oleh beberapa pelanggan.']);
        }

        $perangkat = Perangkat::findOrFail($request->id_perangkat);
        $perangkat->delete();

        return redirect()->route('admin.ont.index')->with('success', 'Data ONT berhasil dihapus!');
    }
}
