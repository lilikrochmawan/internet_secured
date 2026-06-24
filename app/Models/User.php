<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tb_user';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'nama_user',
        'password',
        'level',
        'foto',
        'id_pelanggan',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: User belongs to Pelanggan
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Check if user is staff (admin, kasir, teknisi, sales, mitra)
     */
    public function isStaff()
    {
        return in_array($this->level, ['admin', 'kasir', 'teknisi', 'sales', 'mitra']);
    }

    /**
     * Get branch access configurations for this user
     */
    public function getBranchAccess()
    {
        return \Illuminate\Support\Facades\DB::table('tb_user_branch_access')
            ->where('id_user', $this->id)
            ->get();
    }

    /**
     * Check if user has access to a specific sidebar menu
     */
    public function hasMenuAccess($menuKey)
    {
        // Admin always has access to all menus
        if ($this->level === 'admin') {
            return true;
        }

        return \Illuminate\Support\Facades\DB::table('tb_user_menu_access')
            ->where('id_user', $this->id)
            ->where('menu_key', $menuKey)
            ->exists();
    }

    /**
     * Get menu access list for this user
     */
    public function getMenuAccess()
    {
        return \Illuminate\Support\Facades\DB::table('tb_user_menu_access')
            ->where('id_user', $this->id)
            ->pluck('menu_key')
            ->toArray();
    }
}
