# Laravel Authentication System

## Overview
Sistem autentikasi telah diintegrasikan dengan database Anda menggunakan tabel `tb_pelanggan` dan `tb_user`.

## Komponen Utama

### 1. Models

#### `App\Models\Pelanggan`
Model untuk tabel `tb_pelanggan` yang menyimpan data pelanggan.

**Fitur:**
- Menggunakan `id_pelanggan` sebagai primary key
- Method `findByPhone($phone)` untuk mencari pelanggan berdasarkan nomor HP
- Relationship: `users()` - Satu pelanggan bisa memiliki banyak user

**Struktur Tabel:**
```
- id_pelanggan (PK)
- kode_pelanggan
- nik
- nama_pelanggan
- alamat
- no_telp (nomor HP)
- paket
- ip_address
- tgl_pemasangan
- jatuh_tempo
- location
- id_perangkat
- odp
```

#### `App\Models\User`
Model untuk tabel `tb_user` yang menyimpan data login user.

**Fitur:**
- Menggunakan `id` sebagai primary key
- Menggunakan `tb_user` sebagai table name
- Relationship: `pelanggan()` - Setiap user terhubung ke satu pelanggan

**Struktur Tabel:**
```
- id (PK)
- username
- nama_user
- password (hashed)
- level (admin/user/operator/etc)
- foto
- id_pelanggan (FK ke tb_pelanggan)
- phone_number
```

### 2. Authentication Controller

#### `App\Http\Controllers\AuthController`

**Method: `login(Request $request)`**

Flow login:
1. User memasukkan nomor HP dan password
2. Sistem mencari pelanggan di `tb_pelanggan` berdasarkan nomor HP
3. Jika ketemu, cari user di `tb_user` dengan `id_pelanggan` yang sesuai
4. Verifikasi password user
5. Jika semua valid, login user dan redirect ke dashboard

**Error Handling:**
- Nomor HP tidak ditemukan di tb_pelanggan
- User/akun tidak ditemukan untuk pelanggan tersebut
- Password tidak sesuai

### 3. Login Form

File: `resources/views/auth/login.blade.php`

Input yang diperlukan:
- **Nomor Telepon**: Nomor HP sesuai `tb_pelanggan.no_telp` (contoh: 085747114915)
- **Password**: Password user sesuai `tb_user.password`
- **Ingat Saya**: Checkbox untuk remember me functionality

### 4. Dashboard

File: `resources/views/dashboard.blade.php`

Menampilkan:
- Nama user dari `tb_user.nama_user`
- Nomor HP dari `tb_pelanggan.no_telp`
- Level user dari `tb_user.level`
- Detail paket dari `tb_pelanggan`
- Tombol Logout

## Cara Menggunakan

### Testing Login

1. **Pastikan sudah mengakses database `tagihan_lotus`**
   - DB_DATABASE=tagihan_lotus

2. **Akses halaman login:**
   ```
   http://localhost/laravel-app
   ```

3. **Masukkan kredensial:**
   - Nomor Telepon: 085747114915 (contoh dari data Alif Ulil Amri)
   - Password: Indotel@123 (sesuai tb_user)

### Struktur Autentikasi

```
Login Form (Phone + Password)
         ↓
   findByPhone() → tb_pelanggan
         ↓
   Find User → tb_user (by id_pelanggan)
         ↓
   Hash Check → Password verification
         ↓
   Auth::login()
         ↓
   Redirect to Dashboard
```

## Database Relationship

```
tb_pelanggan (1) ←→ (Many) tb_user
    ↓
id_pelanggan ←→ tb_user.id_pelanggan
```

Contoh data:
- **tb_pelanggan:**
  - id_pelanggan: 2, no_telp: 085747114915, nama_pelanggan: Alif Ulil Amri

- **tb_user:**
  - id: 125, username: r16, nama_user: Alif Ulil Amri, password: [hashed], level: user, id_pelanggan: 2

## Security Notes

1. Password disimpan dalam format hash (bcrypt)
2. Menggunakan CSRF protection pada form login
3. Session dienkripsi untuk remember token
4. Password verification menggunakan `Hash::check()`

## Customization

### Mengubah field yang ditampilkan di Dashboard

Edit `dashboard.blade.php`:
```blade
{{ $user->nama_user }} <!-- Nama user -->
{{ $pelanggan->no_telp }} <!-- Nomor HP -->
{{ $user->level }} <!-- Level user -->
{{ $pelanggan->alamat }} <!-- Alamat -->
```

### Menambah Role-based Access

Di AuthController, tambahkan middleware:
```php
Route::middleware('auth:admin')->group(function () {
    // Routes hanya untuk admin
});
```

### Mengubah Password Hashing

Ubah di User model:
```php
protected function casts(): array
{
    return [
        'password' => 'hashed', // Default bcrypt
    ];
}
```

## Troubleshooting

| Masalah | Solusi |
|--------|--------|
| Login gagal "Nomor HP tidak ditemukan" | Pastikan nomor HP sesuai format di tb_pelanggan |
| Login gagal "Password tidak sesuai" | Verifikasi password di tb_user |
| Akun tidak ditemukan | Pastikan ada data di tb_user dengan id_pelanggan yang sesuai |
| Session error | Clear browser cookies atau restart session |

## Files Modified

1. `app/Models/User.php` - Updated untuk tb_user
2. `app/Models/Pelanggan.php` - Created (baru)
3. `app/Http/Controllers/AuthController.php` - Updated login logic
4. `resources/views/auth/login.blade.php` - Added password field
5. `resources/views/dashboard.blade.php` - Display user info dari database

