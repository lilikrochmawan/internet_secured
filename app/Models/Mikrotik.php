<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mikrotik extends Model
{
    protected $table = 'tbl_mikrotik';
    protected $primaryKey = 'id_mikrotik';
    public $timestamps = false;

    protected $fillable = [
        'id_mikrotik',
        'ip',
        'username',
        'password',
        'port_mikrotik',
    ];
}
