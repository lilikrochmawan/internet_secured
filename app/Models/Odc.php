<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odc extends Model
{
    use HasFactory;

    protected $table = 'tbl_odc';
    protected $primaryKey = 'id_odc';
    public $timestamps = false;

    protected $fillable = [
        'nama_odc',
        'perangkat_odc',
        'port_odc',
        'location',
        'redaman',
        'tube',
        'core_number',
        'jenis_odc',
        'parent_id',
    ];

    public function parentOdc()
    {
        return $this->belongsTo(Odc::class, 'parent_id', 'id_odc');
    }

    public function subOdcs()
    {
        return $this->hasMany(Odc::class, 'parent_id', 'id_odc');
    }

    public function odps()
    {
        return $this->hasMany(Odp::class, 'odc', 'id_odc');
    }
}
