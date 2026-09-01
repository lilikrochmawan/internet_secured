<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    use HasFactory;

    protected $table = 'tb_olt';
    protected $primaryKey = 'id_olt';

    protected $fillable = [
        'nama_olt',
        'ip_address',
        'port',
        'protokol',
        'username',
        'password',
        'snmp_community',
        'tipe_olt',
    ];

    protected $casts = [
        'port' => 'integer',
    ];
}
