<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoMutasi extends Model
{
    use HasFactory;

    protected $table = 'tbl_saldo_mutasi';

    protected $fillable = [
        'id_pelanggan',
        'tipe',
        'jumlah',
        'saldo_awal',
        'saldo_akhir',
        'kategori',
        'keterangan',
        'reference_id',
        'created_by',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
