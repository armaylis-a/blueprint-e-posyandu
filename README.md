# E-Posyandu MVP Akademik

Aplikasi web-mobile berbasis PHP Native, MySQL/MariaDB, dan CSS responsif untuk tahap **MVP akademik**, dengan PWA direncanakan pada tahap berikutnya.

> **Penting:** proyek ini menggunakan data sintetis/demo. Jangan memasukkan NIK, nomor telepon, alamat, atau data pribadi anak/wali nyata.

## Fitur yang sudah tersedia

- Login kader/supervisor berbasis session.
- Password disimpan sebagai hash bcrypt (`password_hash`).
- Prepared statement PDO untuk query akun.
- Regenerasi session ID setelah login.
- CSRF token pada login dan logout.
- Session cookie `HttpOnly`, `SameSite=Lax`, dan opsi `Secure` untuk HTTPS.
- Rate limit login sederhana per session untuk demo.
- Dashboard terlindungi dengan pemeriksaan session.
- Tampilan responsif untuk desktop dan ponsel.
- Database, seed, reset, dan dokumentasi pada folder `database/`.

## Menjalankan di XAMPP

### 1. Start service

1. Start **Apache** dan **MySQL** dari XAMPP.
2. Pastikan database sudah diimpor.

### 2. Import database

melalui phpMyAdmin:

1. Import `database/schema.sql`.
2. Import `database/seed_demo.sql`.
3. Jangan mengimpor `reset_demo.sql` kecuali ingin menghapus data demo.

### 3. Akses aplikasi

Jika project berada di `htdocs`, buka:

```text
http://localhost/nama-folder/login.php
```

Disarankan mengatur document root Apache langsung ke folder project. File `.htaccess` juga memblokir akses web ke `config`, `includes`, `partials`, dan `database`.

## Konfigurasi database

Secara default, `config/database.php` menggunakan konfigurasi XAMPP lokal:

```text
host:     127.0.0.1
port:     3306
database: e_posyandu_mvp
user:     root
password: kosong
```

Untuk environment lain, set variabel lingkungan berikut:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
SESSION_SECURE=true   # hanya saat memakai HTTPS
APP_DEBUG=false
```

Jangan menyimpan kredensial production di dalam file PHP atau repository.

## Akun demo

| Username | Password | Peran |
|---|---|---|
| `kader.demo` | `DemoKader123!` | kader |
| `supervisor.demo` | `DemoSupervisor123!` | supervisor |

## Catatan stunting

Seed `DEMO-IFELSE` hanya mensimulasikan ambang tinggi dari blueprint. Status `demo_indikasi_stunting` dan `demo_normal` **bukan diagnosis klinis**. Sebelum pilot atau produksi, ganti dengan standar antropometri resmi, tabel LMS/z-score, validasi metode ukur, dan persetujuan tenaga kesehatan.

## Struktur Ringkas

```text
config/
  database.php       # koneksi PDO
  session.php        # konfigurasi session
includes/
  bootstrap.php      # bootstrap aplikasi
  functions.php      # helper output/flash
  auth.php           # login, CSRF, authorization
partials/
  header.php
  footer.php
database/
  schema.sql
  seed_demo.sql
  reset_demo.sql
assets/css/style.css
login.php
index.php
logout.php
```

## Langkah berikutnya

1. CRUD balita.
2. Input timbangan dengan perhitungan usia di server.
3. Modul assessor stunting.
4. Dashboard dan laporan.
5. PWA dan pengujian.
