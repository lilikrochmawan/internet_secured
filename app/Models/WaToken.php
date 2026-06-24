<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaToken extends Model
{
    protected $table = 'tbl_token';
    protected $primaryKey = 'id_token';
    public $timestamps = false;

    protected $fillable = [
        'id_token',
        'token',
    ];
}
