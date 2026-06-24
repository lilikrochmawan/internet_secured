<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluhan extends Model
{
    use HasFactory;

    protected $table = 'tbl_keluhan';
    protected $primaryKey = 'id_keluhan';
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'judul_keluhan',
        'nomor_tiket',
        'isi_keluhan',
        'gambar',
        'masalah',
        'no_wa',
        'status_keluhan',
        'tanggal',
        'user_id',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
