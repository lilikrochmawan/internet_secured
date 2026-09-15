<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WabaChat extends Model
{
    use HasFactory;

    protected $table = 'tbl_waba_chat';

    protected $fillable = [
        'no_telp',
        'nama',
        'pesan',
        'media_url',
        'tipe',
        'status',
    ];
}
