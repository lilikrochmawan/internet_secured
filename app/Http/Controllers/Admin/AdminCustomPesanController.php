<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCustomPesanController extends Controller
{
    public function index()
    {
        $notif = DB::table('tbl_notif')->first();
        $blokir = DB::table('tbl_blokir')->first();
        $notifbayar = DB::table('tbl_notifbayar')->first();
        $bukablokir = DB::table('tbl_bukablokir')->first();
        $pemasangan = DB::table('tbl_npemasangan')->first();
        $reminder = DB::table('tbl_notifreminder')->first();

        return view('admin.custom-pesan.index', compact('notif', 'blokir', 'notifbayar', 'bukablokir', 'pemasangan', 'reminder'));
    }

    public function updateNotif(Request $request)
    {
        $request->validate([
            'status' => 'required|string',
            'pesan_notifikasi' => 'required|string',
        ]);

        $exists = DB::table('tbl_notif')->first();
        if ($exists) {
            DB::table('tbl_notif')->update([
                'status_notifikasi' => $request->status,
                'pesan_notifikasi' => $request->pesan_notifikasi,
            ]);
        } else {
            DB::table('tbl_notif')->insert([
                'status_notifikasi' => $request->status,
                'pesan_notifikasi' => $request->pesan_notifikasi,
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan notifikasi tagihan bulanan berhasil diperbarui!');
    }

    public function updateBayar(Request $request)
    {
        $request->validate([
            'pesan_bayar' => 'required|string',
        ]);

        $exists = DB::table('tbl_notifbayar')->first();
        if ($exists) {
            DB::table('tbl_notifbayar')->update([
                'pesan_bayar' => $request->pesan_bayar,
            ]);
        } else {
            DB::table('tbl_notifbayar')->insert([
                'pesan_bayar' => $request->pesan_bayar,
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan bukti pembayaran berhasil diperbarui!');
    }

    public function updatePemasangan(Request $request)
    {
        $request->validate([
            'status_npemasangan' => 'required|string',
            'pesan_npemasangan' => 'required|string',
        ]);

        $exists = DB::table('tbl_npemasangan')->first();
        if ($exists) {
            DB::table('tbl_npemasangan')->update([
                'status_notif' => $request->status_npemasangan,
                'pesan_notif' => $request->pesan_npemasangan,
            ]);
        } else {
            DB::table('tbl_npemasangan')->insert([
                'status_notif' => $request->status_npemasangan,
                'pesan_notif' => $request->pesan_npemasangan,
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan pemasangan awal berhasil diperbarui!');
    }

    public function updateBlokir(Request $request)
    {
        $request->validate([
            'status_blokir' => 'required|string',
            'pesan_blokir' => 'required|string',
        ]);

        $exists = DB::table('tbl_blokir')->first();
        $data = [
            'status_blokir' => $request->status_blokir,
            'pesan_blokir' => $request->pesan_blokir,
        ];

        if ($exists) {
            DB::table('tbl_blokir')->update($data);
        } else {
            DB::table('tbl_blokir')->insert($data);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pengaturan isolir/blokir otomatis berhasil diperbarui!');
    }

    public function updateBukaBlokir(Request $request)
    {
        $request->validate([
            'pesan_bukablokir' => 'required|string',
        ]);

        $exists = DB::table('tbl_bukablokir')->first();
        if ($exists) {
            DB::table('tbl_bukablokir')->update([
                'pesan_bukablokir' => $request->pesan_bukablokir,
            ]);
        } else {
            DB::table('tbl_bukablokir')->insert([
                'pesan_bukablokir' => $request->pesan_bukablokir,
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan unblock/buka blokir berhasil diperbarui!');
    }

    public function updateReminder(Request $request)
    {
        $request->validate([
            'status_reminder' => 'required|string',
            'pesan_reminder' => 'required|string',
        ]);

        $exists = DB::table('tbl_notifreminder')->first();
        if ($exists) {
            DB::table('tbl_notifreminder')->update([
                'status_reminder' => $request->status_reminder,
                'pesan_reminder' => $request->pesan_reminder,
            ]);
        } else {
            DB::table('tbl_notifreminder')->insert([
                'status_reminder' => $request->status_reminder,
                'pesan_reminder' => $request->pesan_reminder,
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan reminder tagihan berhasil diperbarui!');
    }
}
