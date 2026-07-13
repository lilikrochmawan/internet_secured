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
        'status',
    ];

    /**
     * Accessor: only return token value if status is active.
     */
    public function getTokenAttribute($value)
    {
        if (isset($this->attributes['status']) && $this->attributes['status'] !== 'aktif') {
            return null;
        }
        return $value;
    }
}
