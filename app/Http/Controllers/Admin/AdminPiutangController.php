<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;

class AdminPiutangController extends Controller
{
    public function index(Request $request)
    {
        // level: admin, kasir, noc
        $piutangs = Tagihan::with('pelanggan')
            ->where('status_bayar', 2) // 2 = Piutang
            ->orderBy('bulan_tahun', 'desc')
            ->paginate(15);
            
        return view('admin.piutang.index', compact('piutangs'));
    }

    public function bayar(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
        ]);

        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        
        if ($tagihan->status_bayar == 1) {
            return back()->with('error', 'Tagihan sudah terbayar.');
        }

        $tagihan->update([
            'status_bayar' => 1,
            'waktu_bayar' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'user_id' => Auth::id(),
            'terbayar' => $tagihan->jml_bayar,
            'blokir_status' => null,
        ]);

        return back()->with('success', 'Piutang berhasil dibayarkan.');
    }
}
