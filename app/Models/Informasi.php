<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informasi extends Model
{
    use HasFactory;

    protected $table = 'tbl_informasi';
    protected $primaryKey = 'id_informasi';
    public $timestamps = false;

    protected $fillable = [
        'id_informasi',
        'judul_informasi',
        'isi_informasi',
    ];
}
