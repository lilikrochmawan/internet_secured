<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pgate extends Model
{
    use HasFactory;

    protected $table = 'tbl_pgate';
    protected $primaryKey = 'id_pgat';
    public $timestamps = false;

    protected $fillable = [
        'id_pgat',
        'tclientkey',
        'tserverkey',
        'mode',
    ];
}
