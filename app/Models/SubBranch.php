<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubBranch extends Model
{
    use HasFactory;

    protected $table = 'tb_sub_branch';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_branch',
        'nama_sub_branch',
        'deskripsi',
    ];

    /**
     * Relationship: SubBranch belongs to Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_branch', 'id');
    }

    /**
     * Relationship: SubBranch has many Pelanggan
     */
    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class, 'id_sub_branch', 'id');
    }
}
