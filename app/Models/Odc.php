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
    ];

    public function odps()
    {
        return $this->hasMany(Odp::class, 'odc', 'id_odc');
    }
}
