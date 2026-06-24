# Quick Setup Guide - Laravel Authentication

## ✅ Setup Complete

Sistem autentikasi Laravel telah berhasil diintegrasikan dengan database `tagihan_lotus`.

## 🚀 Cara Menggunakan

### 1. Start Laravel Development Server

```bash
cd c:\xampp\htdocs\internet\laravel-app
php artisan serve
```

Server akan berjalan di: `http://127.0.0.1:8000`

### 2. Akses Halaman Login

Buka di browser: `http://127.0.0.1:8000`

### 3. Test Login dengan Akun Sampel

Gunakan salah satu akun dari database:

#### Akun 1 - User Pelanggan
- **Nomor HP**: 085747114915
- **Password**: Indotel@123
- **Nama**: Alif Ulil Amri
- **Level**: user

#### Akun 2 - User Pelanggan
- **Nomor HP**: 08995219353
- **Password**: Indotel@123
- **Nama**: Rina Noviyani
- **Level**: user

#### Akun Admin (jika tersedia)
- **Nomor HP**: 123456778
- **Password**: Espansa70
- **Nama**: admin
- **Level**: admin

### 4. Fitur yang Tersedia

✅ Login dengan nomor HP dari tb_pelanggan
✅ Verifikasi password dari tb_user
✅ Role/Level based pada tb_user.level
✅ Dashboard menampilkan user & pelanggan info
✅ Logout functionality
✅ Remember me checkbox
✅ Error handling & validation

## 📋 Flow Autentikasi

```
1. User input: Nomor HP + Password
2. Sistem cari di tb_pelanggan by nomor HP
3. Sistem cari di tb_user by id_pelanggan
4. Verifikasi password
5. Login jika valid
6. Tampilkan dashboard dengan data dari kedua table
```

## 🔗 Database Connection

File: `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tagihan_lotus
DB_USERNAME=root
DB_PASSWORD=
```

## 📁 File yang Dimodifikasi

| File | Status | Perubahan |
|------|--------|-----------|
| `app/Models/User.php` | ✅ Updated | Menggunakan tb_user table |
| `app/Models/Pelanggan.php` | ✅ Created | Model baru untuk tb_pelanggan |
| `app/Http/Controllers/AuthController.php` | ✅ Updated | Login logic dengan join tb_pelanggan & tb_user |
| `resources/views/auth/login.blade.php` | ✅ Updated | Tambah password field |
| `resources/views/dashboard.blade.php` | ✅ Updated | Tampil data user & pelanggan |
| `AUTHENTICATION.md` | ✅ Created | Dokumentasi lengkap sistem |

## 🆘 Troubleshooting

### Masalah: "Nomor HP tidak ditemukan"
- Pastikan nomor HP sesuai dengan format di database (tanpa spasi/simbol)
- Cek di tb_pelanggan.no_telp

### Masalah: "Akun tidak ditemukan"
- User harus ada di tb_user
- User harus memiliki id_pelanggan yang menunjuk ke tb_pelanggan
- Cek data di kedua table

### Masalah: "Password tidak sesuai"
- Password di tb_user harus dalam format hashed (bcrypt)
- Gunakan password yang sudah ada di database
- Contoh: Indotel@123

### Masalah: Database Connection Error
- Pastikan XAMPP MySQL sudah berjalan
- Pastikan kredensial di .env sesuai
- Database `tagihan_lotus` sudah ada dan accessible

## 📝 Cara Menambah User Baru

Untuk menambah user baru dengan cara manual di database:

### 1. Tambah di tb_pelanggan (jika pelanggan baru)
```sql
INSERT INTO tb_pelanggan (kode_pelanggan, nama_pelanggan, alamat, no_telp, paket, tgl_pemasangan)
VALUES ('WNG031999', 'Nama Pelanggan', 'Alamat', '0899xxxxxxxx', 29, NOW());
```

### 2. Tambah di tb_user
```sql
INSERT INTO tb_user (username, nama_user, password, level, id_pelanggan)
VALUES ('username', 'Nama Pelanggan', '$2y$12$...', 'user', [id_pelanggan]);
```

**Note**: Password harus di-hash terlebih dahulu menggunakan bcrypt.

## 🔒 Security

- ✅ Password di-hash dengan bcrypt
- ✅ CSRF protection pada form
- ✅ Session management
- ✅ Input validation
- ✅ Password verification

## 📚 Dokumentasi Lengkap

Lihat file `AUTHENTICATION.md` untuk dokumentasi detail.

---

**Version**: 1.0
**Last Updated**: 2025-05-21
**By**: Laravel Authentication Integration
