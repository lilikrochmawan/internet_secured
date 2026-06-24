<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    use HasFactory;

    protected $table = 'tb_perangkat';
    protected $primaryKey = 'id_perangkat';
    public $timestamps = false;

    protected $fillable = [
        'nama_perangkat',
    ];

    /**
     * Relationship: Perangkat has many customers (pelanggan)
     */
    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'id_perangkat', 'id_perangkat');
    }
}
