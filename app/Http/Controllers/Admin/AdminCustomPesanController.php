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
        $promo = DB::table('tbl_notifpromo')->first();

        return view('admin.custom-pesan.index', compact('notif', 'blokir', 'notifbayar', 'bukablokir', 'pemasangan', 'reminder', 'promo'));
    }

    public function updateNotif(Request $request)
    {
        $request->validate([
            'status' => 'required|string',
            'pesan_notifikasi' => 'required|string',
            'template_name' => 'nullable|string',
            'template_params' => 'nullable|string',
            'template_language' => 'nullable|string',
        ]);

        $exists = DB::table('tbl_notif')->first();
        if ($exists) {
            DB::table('tbl_notif')->update([
                'status_notifikasi' => $request->status,
                'pesan_notifikasi' => $request->pesan_notifikasi,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        } else {
            DB::table('tbl_notif')->insert([
                'status_notifikasi' => $request->status,
                'pesan_notifikasi' => $request->pesan_notifikasi,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan notifikasi tagihan bulanan berhasil diperbarui!');
    }

    public function updateBayar(Request $request)
    {
        $request->validate([
            'pesan_bayar' => 'required|string',
            'template_name' => 'nullable|string',
            'template_params' => 'nullable|string',
            'template_language' => 'nullable|string',
        ]);

        $exists = DB::table('tbl_notifbayar')->first();
        if ($exists) {
            DB::table('tbl_notifbayar')->update([
                'pesan_bayar' => $request->pesan_bayar,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        } else {
            DB::table('tbl_notifbayar')->insert([
                'pesan_bayar' => $request->pesan_bayar,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan bukti pembayaran berhasil diperbarui!');
    }

    public function updatePemasangan(Request $request)
    {
        $request->validate([
            'status_npemasangan' => 'required|string',
            'pesan_npemasangan' => 'required|string',
            'template_name' => 'nullable|string',
            'template_params' => 'nullable|string',
            'template_language' => 'nullable|string',
        ]);

        $exists = DB::table('tbl_npemasangan')->first();
        if ($exists) {
            DB::table('tbl_npemasangan')->update([
                'status_notif' => $request->status_npemasangan,
                'pesan_notif' => $request->pesan_npemasangan,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        } else {
            DB::table('tbl_npemasangan')->insert([
                'status_notif' => $request->status_npemasangan,
                'pesan_notif' => $request->pesan_npemasangan,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan pemasangan awal berhasil diperbarui!');
    }

    public function updateBlokir(Request $request)
    {
        $request->validate([
            'status_blokir' => 'required|string',
            'pesan_blokir' => 'required|string',
            'template_name' => 'nullable|string',
            'template_params' => 'nullable|string',
            'template_language' => 'nullable|string',
        ]);

        $exists = DB::table('tbl_blokir')->first();
        $data = [
            'status_blokir' => $request->status_blokir,
            'pesan_blokir' => $request->pesan_blokir,
            'template_name' => $request->template_name,
            'template_params' => $request->template_params,
            'template_language' => $request->template_language ?? 'id',
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
            'template_name' => 'nullable|string',
            'template_params' => 'nullable|string',
            'template_language' => 'nullable|string',
        ]);

        $exists = DB::table('tbl_bukablokir')->first();
        if ($exists) {
            DB::table('tbl_bukablokir')->update([
                'pesan_bukablokir' => $request->pesan_bukablokir,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        } else {
            DB::table('tbl_bukablokir')->insert([
                'pesan_bukablokir' => $request->pesan_bukablokir,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan unblock/buka blokir berhasil diperbarui!');
    }

    public function updateReminder(Request $request)
    {
        $request->validate([
            'status_reminder' => 'required|string',
            'pesan_reminder' => 'required|string',
            'template_name' => 'nullable|string',
            'template_params' => 'nullable|string',
            'template_language' => 'nullable|string',
        ]);

        $exists = DB::table('tbl_notifreminder')->first();
        if ($exists) {
            DB::table('tbl_notifreminder')->update([
                'status_reminder' => $request->status_reminder,
                'pesan_reminder' => $request->pesan_reminder,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        } else {
            DB::table('tbl_notifreminder')->insert([
                'status_reminder' => $request->status_reminder,
                'pesan_reminder' => $request->pesan_reminder,
                'template_name' => $request->template_name,
                'template_params' => $request->template_params,
                'template_language' => $request->template_language ?? 'id',
            ]);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan reminder tagihan berhasil diperbarui!');
    }

    public function updatePromo(Request $request)
    {
        $request->validate([
            'status_promo' => 'required|string',
            'pesan_promo' => 'required|string',
            'template_name' => 'nullable|string',
            'template_params' => 'nullable|string',
            'template_language' => 'nullable|string',
        ]);

        $exists = DB::table('tbl_notifpromo')->first();
        $data = [
            'status_promo' => $request->status_promo,
            'pesan_promo' => $request->pesan_promo,
            'template_name' => $request->template_name,
            'template_params' => $request->template_params,
            'template_language' => $request->template_language ?? 'id',
            'updated_at' => now(),
        ];

        if ($exists) {
            DB::table('tbl_notifpromo')->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('tbl_notifpromo')->insert($data);
        }

        return redirect()->route('admin.custom_pesan.index')->with('success', 'Pesan template Promo WhatsApp berhasil diperbarui!');
    }
    public function fetchWabaImage(Request $request)
    {
        $templateName = $request->get('template_name');
        if (!$templateName) {
            return response()->json(['success' => false, 'message' => 'Nama template tidak diberikan']);
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->bablast_token)) {
            return response()->json(['success' => false, 'message' => 'Token Bablast belum diatur']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $tokenInfo->bablast_token,
                'Content-Type' => 'application/json'
            ])->get('https://api.bablast.id/waba/templates');

            $data = $response->json();
            if(isset($data['data'])) {
                foreach($data['data'] as $tpl) {
                    if ($tpl['name'] === $templateName) {
                        foreach ($tpl['components'] as $comp) {
                            if ($comp['type'] === 'HEADER' && $comp['format'] === 'IMAGE') {
                                if (isset($comp['example']['header_handle'][0])) {
                                    return response()->json([
                                        'success' => true, 
                                        'link' => $comp['example']['header_handle'][0]
                                    ]);
                                }
                            }
                        }
                        return response()->json(['success' => false, 'message' => 'Template ini tidak memiliki header gambar di server Meta.']);
                    }
                }
                return response()->json(['success' => false, 'message' => 'Template tidak ditemukan di server Bablast.']);
            }
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data dari server Bablast.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
