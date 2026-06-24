<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPemasangan extends Model
{
    use HasFactory;

    protected $table = 'tbl_order_pemasangan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nik',
        'nama',
        'no_telp',
        'paket',
        'alamat_ktp',
        'alamat_pemasangan',
        'koordinat_pemasangan',
        'jadwal_pemasangan',
        'foto_ktp',
        'foto_dokumentasi',
        'status',
        'id_sales',
        'id_teknisi',
    ];

    /**
     * Relationship: Order belongs to a Package
     */
    public function paketDetail()
    {
        return $this->belongsTo(Paket::class, 'paket', 'id_paket');
    }

    /**
     * Relationship: Order belongs to Sales (User)
     */
    public function sales()
    {
        return $this->belongsTo(User::class, 'id_sales', 'id');
    }

    /**
     * Relationship: Order belongs to Technician (User)
     */
    public function teknisi()
    {
        return $this->belongsTo(User::class, 'id_teknisi', 'id');
    }
}
