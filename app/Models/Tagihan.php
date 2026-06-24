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
}

