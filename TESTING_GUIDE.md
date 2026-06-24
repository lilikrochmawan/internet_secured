# 🚀 Panduan Lengkap - Setup & Testing Authentication Laravel

## 📋 Prerequisites

1. ✅ XAMPP sudah terinstall dan MySQL berjalan
2. ✅ Database `tagihan_lotus` sudah ada
3. ✅ PHP 8.1+ terinstall
4. ✅ Composer terinstall

## 🛠️ Step-by-Step Setup

### Step 1: Navigate ke Direktori Laravel App

```bash
cd c:\xampp\htdocs\internet\laravel-app
```

### Step 2: Install/Update Dependencies (jika belum)

```bash
composer install
```

### Step 3: Create Sessions Table (Opsional - jika belum ada)

Run migration untuk membuat tabel sessions yang diperlukan untuk database session driver:

```bash
php artisan migrate
```

**Jika sudah ada tables, jalankan:**
```bash
php artisan migrate --path=database/migrations/2025_05_21_000000_create_sessions_table.php
```

### Step 4: Mulai Development Server

```bash
php artisan serve
```

**Output yang diharapkan:**
```
Laravel development server started:
  Local: http://127.0.0.1:8000
  Network: use --host 0.0.0.0 to allow external connections
```

### Step 5: Test Login

Buka browser dan akses: `http://127.0.0.1:8000`

## ✅ Test Skenario Login

### Test 1: Login Berhasil (User Standar)

**Input:**
- Nomor HP: `085747114915`
- Password: `Indotel@123`

**Expected Output:**
- ✅ Redirect ke dashboard
- ✅ Tampil nama: "Alif Ulil Amri"
- ✅ Tampil nomor HP: "085747114915"
- ✅ Tampil level: "user"
- ✅ Tampil alamat: "Perum Taman Harmoni Blok R16"

### Test 2: Nomor HP Tidak Ditemukan

**Input:**
- Nomor HP: `088888888888` (tidak ada di database)
- Password: `Indotel@123`

**Expected Output:**
- ❌ Error: "Nomor HP tidak ditemukan dalam sistem"
- Tetap di halaman login

### Test 3: Password Salah

**Input:**
- Nomor HP: `085747114915`
- Password: `salahpassword`

**Expected Output:**
- ❌ Error: "Password tidak sesuai"
- Tetap di halaman login

### Test 4: Logout

Di dashboard:
1. Klik tombol "Logout"
2. **Expected Output:**
   - ✅ Redirect ke halaman login
   - ✅ Session dihapus
   - ✅ Bisa login dengan akun lain

### Test 5: Remember Me

**Input:**
- Nomor HP: `085747114915`
- Password: `Indotel@123`
- ✅ Centang "Ingat Saya"

**Expected Output:**
- ✅ Login berhasil
- ✅ Session tersimpan untuk login berikutnya

## 🧪 Test Akun Lain

Coba dengan akun pelanggan lainnya:

```
085747114915 - Indotel@123 (Alif Ulil Amri)
08995219353  - Indotel@123 (Rina Noviyani)
085725646575 - Indotel@123 (Winda Hatmanti N)
081249522117 - Indotel@123 (Dika Suryanto)
085781642968 - Indotel@123 (Yohanes Putra Perdana)
```

## 📊 Verificasi Data di Database

### Cek tb_pelanggan

```sql
SELECT id_pelanggan, kode_pelanggan, nama_pelanggan, no_telp, alamat, paket 
FROM tb_pelanggan 
LIMIT 10;
```

**Expected columns:**
- `id_pelanggan` (Primary Key)
- `no_telp` (nomor HP untuk login)
- `nama_pelanggan`
- `alamat`

### Cek tb_user

```sql
SELECT id, username, nama_user, level, id_pelanggan, password 
FROM tb_user 
LIMIT 10;
```

**Expected columns:**
- `id` (Primary Key)
- `username`
- `nama_user`
- `password` (hashed - format: $2y$12$...)
- `level` (admin/user/operator)
- `id_pelanggan` (Foreign Key ke tb_pelanggan)

### Cek Join kedua table

```sql
SELECT 
  p.id_pelanggan,
  p.nama_pelanggan,
  p.no_telp,
  u.id,
  u.username,
  u.level
FROM tb_pelanggan p
LEFT JOIN tb_user u ON p.id_pelanggan = u.id_pelanggan
LIMIT 10;
```

## 🔍 Debugging

### Jika Login Tidak Berhasil

1. **Check Session Configuration** (.env)
   ```
   SESSION_DRIVER=database
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tagihan_lotus
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. **Check Laravel Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

4. **Verify Sessions Table**
   ```sql
   SHOW TABLES LIKE 'sessions';
   DESC sessions;
   ```

5. **Check CSRF Token**
   - Buka halaman login
   - Check di browser DevTools > Network > login.blade.php
   - Pastikan @csrf token ada

### Jika Database Connection Error

```bash
# Test database connection
php artisan db
```

Jika error:
1. Pastikan XAMPP MySQL sudah running
2. Check .env DATABASE credentials
3. Pastikan database `tagihan_lotus` sudah exist

```bash
# Lihat daftar database
php artisan tinker
>>> DB::select('SHOW DATABASES;')
```

## 📝 Manual Testing dengan Artisan Tinker

```bash
# Buka Tinker shell
php artisan tinker
```

### Test Cari Pelanggan by Phone

```php
>>> use App\Models\Pelanggan;
>>> Pelanggan::findByPhone('085747114915');
```

Expected: Object Pelanggan dengan data Alif Ulil Amri

### Test Cari User by id_pelanggan

```php
>>> use App\Models\User;
>>> User::where('id_pelanggan', 2)->first();
```

Expected: User dengan username 'r16'

### Test Password Verification

```php
>>> use Illuminate\Support\Facades\Hash;
>>> $user = User::find(125);
>>> Hash::check('Indotel@123', $user->password);
```

Expected: `true` atau `false` tergantung password

## 🎯 Checklist Verifikasi

- [ ] Database `tagihan_lotus` terkoneksi
- [ ] Tabel `tb_pelanggan` ada dan punya data
- [ ] Tabel `tb_user` ada dan punya data dengan id_pelanggan
- [ ] Tabel `sessions` sudah di-migrate
- [ ] Model `Pelanggan` ada di `app/Models/`
- [ ] Model `User` sudah updated untuk tb_user
- [ ] Controller `AuthController` sudah updated
- [ ] Login form punya field nomor HP dan password
- [ ] Dashboard menampilkan user & pelanggan info
- [ ] Logout button ada di dashboard
- [ ] Tidak ada error di `storage/logs/laravel.log`

## 🆘 Quick Troubleshooting Table

| Problem | Solution |
|---------|----------|
| Halaman login blank | `php artisan view:clear` |
| 500 Error | Check `storage/logs/laravel.log` |
| Session tidak menyimpan | Pastikan tabel `sessions` exist di database |
| Login gagal semua akun | Cek nomor HP format dan password di database |
| CSRF token error | Clear cache dan restart browser |
| Database not found | Pastikan MySQL running dan database exist |
| Model not found | Run `composer dump-autoload` |

## 📞 Support

Jika masih ada issue:

1. Check log file: `storage/logs/laravel.log`
2. Read: `AUTHENTICATION.md` untuk detail teknis
3. Verify data di database via phpMyAdmin
4. Restart development server: `php artisan serve`

---

**Version**: 1.0
**Last Updated**: 2025-05-21
**Status**: ✅ Ready for Testing
