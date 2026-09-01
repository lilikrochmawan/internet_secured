<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odp extends Model
{
    use HasFactory;

    protected $table = 'tbl_odp';
    protected $primaryKey = 'id_odp';
    public $timestamps = false;

    protected $fillable = [
        'nama_odp',
        'port_odp',
        'location',
        'odc',
        'has_ratio',
        'parent_odp_id',
    ];

    public function parentOdp()
    {
        return $this->belongsTo(Odp::class, 'parent_odp_id', 'id_odp');
    }

    public function childOdps()
    {
        return $this->hasMany(Odp::class, 'parent_odp_id', 'id_odp');
    }

    public function odcDetail()
    {
        return $this->belongsTo(Odc::class, 'odc', 'id_odc');
    }

    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class, 'odp', 'id_odp');
    }
}
