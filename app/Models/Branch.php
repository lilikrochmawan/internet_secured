<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'tb_branch';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nama_branch',
        'deskripsi',
    ];

    /**
     * Relationship: Branch has many SubBranches
     */
    public function subBranches()
    {
        return $this->hasMany(SubBranch::class, 'id_branch', 'id');
    }

    /**
     * Relationship: Branch has many Pelanggan
     */
    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class, 'id_branch', 'id');
    }
}
