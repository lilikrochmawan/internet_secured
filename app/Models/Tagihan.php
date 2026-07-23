<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tb_tagihan';
    protected $primaryKey = 'id_tagihan';
    public $timestamps = false;

    protected $fillable = [
        'id_tagihan',
        'id_pelanggan',
        'bulan_tahun',
        'jml_bayar',
        'terbayar',
        'tgl_bayar',
        'status_bayar',
        'no_invoice',
        'blokir_status',
        'terkirim',
        'waktu_bayar',
        'user_id',
        'manual_invoice',
        'bea_pemasangan',
        'jasa_troubleshooting',
        'lain_lain',
        'item_tagihan',
        'jatuh_tempo',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Allocate commission to partners who have access to this paid billing region.
     */
    public static function allocateMitraCommission($id_tagihan)
    {
        $tagihan = self::with('pelanggan')->find($id_tagihan);
        if (!$tagihan || !$tagihan->pelanggan) {
            return;
        }

        $pelanggan = $tagihan->pelanggan;
        $id_branch = $pelanggan->id_branch;
        $id_sub_branch = $pelanggan->id_sub_branch;

        if (is_null($id_branch)) {
            return;
        }

        // Get all mitra users
        $mitras = \Illuminate\Support\Facades\DB::table('tb_user')
            ->where('level', 'mitra')
            ->get();

        foreach ($mitras as $mitra) {
            // Check if this mitra has access to customer's branch/sub-branch
            $hasAccess = \Illuminate\Support\Facades\DB::table('tb_user_branch_access')
                ->where('id_user', $mitra->id)
                ->where('id_branch', $id_branch)
                ->where(function ($query) use ($id_sub_branch) {
                    $query->whereNull('id_sub_branch')
                          ->orWhere('id_sub_branch', $id_sub_branch);
                })
                ->exists();

            if (!$hasAccess) {
                continue;
            }

            // Check if commission is already logged for this partner on this bill to avoid duplicate entry
            $alreadyLogged = \Illuminate\Support\Facades\DB::table('tbl_mitra_komisi_logs')
                ->where('id_user', $mitra->id)
                ->where('id_tagihan', $tagihan->id_tagihan)
                ->exists();

            if ($alreadyLogged) {
                continue;
            }

            // Get commission configuration for this mitra
            $config = \Illuminate\Support\Facades\DB::table('tbl_mitra_config')
                ->where('id_user', $mitra->id)
                ->first();

            if (!$config) {
                continue;
            }

            $komisi_diterima = 0;
            if ($config->tipe_komisi === 'flat') {
                $komisi_diterima = $config->nilai_komisi;
            } elseif ($config->tipe_komisi === 'persentase') {
                $komisi_diterima = ($config->nilai_komisi / 100) * $tagihan->jml_bayar;
            }

            if ($komisi_diterima <= 0) {
                continue;
            }

            // Log commission
            \Illuminate\Support\Facades\DB::table('tbl_mitra_komisi_logs')->insert([
                'id_user' => $mitra->id,
                'id_tagihan' => $tagihan->id_tagihan,
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'jumlah_bayar' => $tagihan->jml_bayar,
                'tipe_komisi' => $config->tipe_komisi,
                'nilai_komisi' => $config->nilai_komisi,
                'komisi_diterima' => $komisi_diterima,
                'created_at' => now(),
            ]);

            // Automatically log as expense in tb_kas
            $ket = "Bagi Hasil Mitra (" . $mitra->nama_user . ") - Tagihan ID: " . $tagihan->id_tagihan . " - Pelanggan: " . $pelanggan->nama_pelanggan;
            \Illuminate\Support\Facades\DB::table('tb_kas')->insert([
                'tgl_kas' => date('Y-m-d'),
                'keterangan' => $ket,
                'penerimaan' => 0,
                'pengeluaran' => $komisi_diterima,
                'id_tagihan' => $tagihan->id_tagihan,
                'status' => 1
            ]);
        }
    }
}

