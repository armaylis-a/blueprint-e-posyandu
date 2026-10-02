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
- CRUD data balita: daftar, pencarian, tambah, edit, detail, dan arsip soft delete.
- Input timbangan dengan perhitungan usia server-side dan assessor demo IF-ELSE.
- Laporan bulanan dengan filter periode, ringkasan, dan tampilan cetak.
- Validasi server-side, prepared statement, CSRF, RBAC, dan audit log untuk perubahan data.
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
  functions.php      # helper output/flash/url
  auth.php           # login, CSRF, authorization
  children.php       # validasi dan operasi domain balita
  assessment.php     # usia dan assessor demo
partials/
  header.php
  footer.php
database/
  schema.sql
  seed_demo.sql
  reset_demo.sql
modules/balita/
  index.php          # daftar, pencarian, filter
  form.php           # tambah/edit
  simpan.php         # endpoint POST create/update
  detail.php         # detail dan riwayat
  hapus.php          # arsip soft delete
modules/timbangan/
  index.php          # riwayat timbangan
  form.php           # input pengukuran
  simpan.php         # validasi, simpan, assessor demo
modules/laporan/
  index.php          # laporan bulanan dan filter periode
  cetak.php          # tampilan cetak/PDF dari browser
includes/reports.php  # query dan ringkasan laporan
assets/js/app.js       # tombol cetak browser
assets/css/style.css
login.php
index.php
logout.php
```

## Langkah berikutnya

1. PWA dan mode offline informasi gizi.
2. Pengujian boundary, keamanan, dan UAT.
3. Validasi algoritma standar WHO/Kemenkes sebelum pilot.
4. Ekspor PDF/CSV dan improve laporan operasional.
