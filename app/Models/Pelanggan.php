<?php

namespace App\Models;

use App\Models\Paket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'tb_pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'kode_pelanggan',
        'nik',
        'nama_pelanggan',
        'alamat',
        'no_telp',
        'paket',
        'ip_address',
        'tgl_pemasangan',
        'jatuh_tempo',
        'location',
        'id_perangkat',
        'odp',
        'id_mikrotik',
        'id_branch',
        'id_sub_branch',
    ];

    /**
     * Relationship: Pelanggan has many users
     */
    public function users()
    {
        return $this->hasMany(User::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function paketDetail()
    {
        return $this->belongsTo(Paket::class, 'paket', 'id_paket');
    }

    public function odpDetail()
    {
        return $this->belongsTo(Odp::class, 'odp', 'id_odp');
    }

    /**
     * Find pelanggan by phone number
     */
    public static function findByPhone($phone)
    {
        return self::getAllByPhone($phone)->first();
    }

    /**
     * Semua pelanggan dengan nomor HP yang sama (dinormalisasi).
     */
    public static function getAllByPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);

        if ($phone === '') {
            return collect();
        }

        // Variasi format nomor telepon yang umum disimpan
        $variations = [$phone];
        if (str_starts_with($phone, '62')) {
            $variations[] = '0' . substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $variations[] = '62' . substr($phone, 1);
        }

        $query = self::whereIn('no_telp', $variations);

        // Pencarian dengan pencocokan akhiran/suffix untuk keamanan data (min 9 digit terakhir)
        if (strlen($phone) >= 9) {
            $suffix = substr($phone, -9);
            $query->orWhere('no_telp', 'like', '%' . $suffix);
        }

        return $query->get();
    }


    public static function getIdsBySamePhone(?string $noTelp): array
    {
        return self::getAllByPhone($noTelp)
            ->pluck('id_pelanggan')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Relationship: Pelanggan belongs to Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_branch', 'id');
    }

    /**
     * Relationship: Pelanggan belongs to SubBranch
     */
    public function subBranch()
    {
        return $this->belongsTo(SubBranch::class, 'id_sub_branch', 'id');
    }

    /**
     * Scope: filter clients based on user branch access.
     * Admin has full access. Staff have access restricted to tb_user_branch_access values.
     */
    public function scopeAllowedForUser($query, $user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return $query->whereRaw('1=0');
        }

        if ($user->level === 'admin') {
            return $query;
        }

        $access = \Illuminate\Support\Facades\DB::table('tb_user_branch_access')
            ->where('id_user', $user->id)
            ->get();

        if ($access->isEmpty()) {
            return $query->whereRaw('1=0');
        }

        return $query->where(function ($q) use ($access) {
            foreach ($access as $acc) {
                $q->orWhere(function ($subQ) use ($acc) {
                    $subQ->where('id_branch', $acc->id_branch);
                    if (!is_null($acc->id_sub_branch)) {
                        $subQ->where('id_sub_branch', $acc->id_sub_branch);
                    }
                });
            }
        });
    }
}
