<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminPromoController extends Controller
{
    public function index(Request $request)
    {
        $query = Promo::with(['pelanggan', 'paket']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_promo', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function($pq) use ($search) {
                      $pq->where('nama_pelanggan', 'like', "%{$search}%")
                         ->orWhere('kode_pelanggan', 'like', "%{$search}%");
                  });
            });
        }

        $promos = $query->paginate(10);

        return view('admin.promo.index', compact('promos'));
    }

    public function create()
    {
        $pelanggan = Pelanggan::with('paketDetail')
            ->allowedForUser()
            ->orderBy('nama_pelanggan', 'asc')
            ->get();

        return view('admin.promo.create', compact('pelanggan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_promo' => 'required|string|max:150',
            'id_pelanggan' => 'required|integer',
            'mulai_bulan' => 'required|integer|between:1,12',
            'mulai_tahun' => 'required|integer',
            'selesai_bulan' => 'required|integer|between:1,12',
            'selesai_tahun' => 'required|integer',
            'nominal_tagihan' => 'required|numeric|min:0',
        ]);

        $pelanggan = Pelanggan::with('paketDetail')->findOrFail($request->id_pelanggan);

        // Start target period from the promo's start date
        $targetMonth = sprintf('%02d', $request->mulai_bulan);
        $targetYear = (string)$request->mulai_tahun;

        $currMonth = date('m');
        $currYear = date('Y');

        // Only apply the "already paid shifting logic" if the promo starts in the current month
        if ($targetMonth === $currMonth && $targetYear === $currYear) {
            $currPeriod = $currMonth . $currYear;
            $isPaidThisMonth = Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->where('bulan_tahun', $currPeriod)
                ->where('status_bayar', 1)
                ->exists();

            if ($isPaidThisMonth) {
                // Shift target to next month
                $carbon = Carbon::now()->addMonth();
                $targetMonth = $carbon->format('m');
                $targetYear = $carbon->format('Y');
            }
        }

        $targetPeriod = $targetMonth . $targetYear;

        DB::beginTransaction();

        try {
            // Save Promo Record
            $promo = Promo::create([
                'nama_promo' => $request->nama_promo,
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'id_paket' => $pelanggan->paket,
                'mulai_bulan' => $request->mulai_bulan,
                'mulai_tahun' => $request->mulai_tahun,
                'selesai_bulan' => $request->selesai_bulan,
                'selesai_tahun' => $request->selesai_tahun,
                'nominal_tagihan' => $request->nominal_tagihan,
            ]);

            // Delete any existing unpaid invoice for the target period to prevent duplicates
            Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->where('bulan_tahun', $targetPeriod)
                ->where(function($q) {
                    $q->whereNull('status_bayar')->orWhere('status_bayar', '!=', 1);
                })
                ->delete();

            // Calculate due date (jatuh tempo)
            $settings = DB::table('tb_profile')->first();
            $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
            $default_hari = $settings->hari_jatuh_tempo ?? 10;
            $sistem = $settings->sistem_billing ?? 'prabayar';

            $target_date = Carbon::create((int)$targetYear, (int)$targetMonth, 1);
            if ($sistem === 'pascabayar') {
                $target_date->addMonth();
            }
            $due_year = $target_date->year;
            $due_month = $target_date->month;
            $due_day = $default_hari;

            if ($pelanggan->jatuh_tempo) {
                $due_day = (int) date('d', strtotime($pelanggan->jatuh_tempo));
            } elseif ($tipe === 'tanggal_pasang' && !empty($pelanggan->tgl_pemasangan)) {
                $due_day = (int) date('d', strtotime($pelanggan->tgl_pemasangan));
            }

            $days_in_month = (int) date('t', strtotime($due_year . '-' . sprintf('%02d', $due_month) . '-01'));
            if ($due_day > $days_in_month) {
                $due_day = $days_in_month;
            }

            $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $due_year, $due_month, $due_day);

            // Create Unpaid Tagihan (Persis seperti transaksi manual)
            $tagihan = Tagihan::create([
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'bulan_tahun' => $targetPeriod,
                'jml_bayar' => $request->nominal_tagihan,
                'terbayar' => 0,
                'status_bayar' => null, // Belum Lunas
                'waktu_bayar' => null,
                'user_id' => Auth::id(),
                'manual_invoice' => 1,
                'item_tagihan' => "Tagihan Awal Promo: " . $request->nama_promo,
                'jatuh_tempo' => $tgl_jatuh_tempo,
            ]);

            // Update customer's due date
            $pelanggan->update([
                'jatuh_tempo' => $tgl_jatuh_tempo
            ]);

            // Send Custom Promo WhatsApp Message
            try {
                $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
                $notifPromo = DB::table('tbl_notifpromo')->where('status_promo', 'aktif')->first();

                if ($tokenInfo && !empty($tokenInfo->token) && $notifPromo && !empty($notifPromo->pesan_promo) && !empty($pelanggan->no_telp)) {
                    $pesan = $notifPromo->pesan_promo;
                    $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
                    $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
                    $pesan = str_replace('$nama_promo', $request->nama_promo, $pesan);
                    $pesan = str_replace('$tagihan', number_format($request->nominal_tagihan, 0, ',', '.'), $pesan);

                    $mulaiFormat = Carbon::create((int)$request->mulai_tahun, (int)$request->mulai_bulan, 1)->translatedFormat('F Y');
                    $selesaiFormat = Carbon::create((int)$request->selesai_tahun, (int)$request->selesai_bulan, 1)->translatedFormat('F Y');

                    $pesan = str_replace('$mulai_promo', $mulaiFormat, $pesan);
                    $pesan = str_replace('$selesai_promo', $selesaiFormat, $pesan);

                    Http::timeout(10)->withHeaders([
                        'Authorization' => $tokenInfo->token
                    ])->asForm()->post('https://api.fonnte.com/send', [
                        'target' => $pelanggan->no_telp,
                        'message' => $pesan,
                        'countryCode' => '62'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Promo WhatsApp Notification Error: ' . $e->getMessage());
            }

            DB::commit();

            Log::info("Staff [" . auth()->user()->nama_user . "] (level: " . auth()->user()->level . ") MENAMBAH PROMO [" . $request->nama_promo . "] untuk pelanggan [" . $pelanggan->nama_pelanggan . "] dengan tagihan awal Rp " . number_format($request->nominal_tagihan, 0, ',', '.') . ".");

            return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil disimpan dan WhatsApp notifikasi telah dikirim.');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Promo Store Error: ' . $ex->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan promo: ' . $ex->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $namaPromo = $promo->nama_promo;
        $pelanggan = Pelanggan::find($promo->id_pelanggan);
        $namaPelanggan = $pelanggan ? $pelanggan->nama_pelanggan : 'Unknown';

        $promo->delete();

        Log::info("Staff [" . auth()->user()->nama_user . "] (level: " . auth()->user()->level . ") MENGHAPUS PROMO [" . $namaPromo . "] pelanggan [" . $namaPelanggan . "].");

        return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil dihapus.');
    }
}
