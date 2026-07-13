<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'tb_promo';
    protected $primaryKey = 'id_promo';

    protected $fillable = [
        'nama_promo',
        'id_pelanggan',
        'id_paket',
        'mulai_bulan',
        'mulai_tahun',
        'selesai_bulan',
        'selesai_tahun',
        'nominal_tagihan',
    ];

    /**
     * Relationship: Promo belongs to Pelanggan
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    /**
     * Relationship: Promo belongs to Paket
     */
    public function paket()
    {
        return $this->belongsTo(Paket::class, 'id_paket', 'id_paket');
    }

    /**
     * Check if there is an active promo for the customer at the target month & year.
     * Calculated using formula: (year * 12) + month to compare ranges.
     */
    public static function getActivePromoForPeriod($id_pelanggan, $month, $year)
    {
        $targetValue = ((int)$year * 12) + (int)$month;

        return self::where('id_pelanggan', $id_pelanggan)
            ->whereRaw('(`mulai_tahun` * 12 + `mulai_bulan`) <= ?', [$targetValue])
            ->whereRaw('(`selesai_tahun` * 12 + `selesai_bulan`) >= ?', [$targetValue])
            ->first();
    }
}
