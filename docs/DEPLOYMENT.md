# Deployment Guide

Dokumen ini disusun untuk operasional MVP akademik dan persiapan pilot/produksi. Tidak ada data nyata yang boleh dimasukkan pada MVP demo.

## 1. Kebutuhan runtime

- PHP 8.1 atau lebih baru dengan ekstensi `pdo_mysql` dan `mbstring`.
- MySQL 8 atau MariaDB 10.6+.
- Web server Apache atau Nginx.
- HTTPS untuk PWA dan session production.
- Database backup storage yang terpisah dari server aplikasi.

## 2. Environment

| Variabel | Local | Production |
|---|---|---|
| `APP_ENV` | development | production |
| `APP_DEBUG` | false | false |
| `DB_HOST` | 127.0.0.1 | host database privat |
| `DB_PORT` | 3306 | 3306 |
| `DB_NAME` | e_posyandu_mvp | nama database production |
| `DB_USER` | root | pengguna dengan hak minimum |
| `DB_PASSWORD` | kosong | manager rahasia |
| `SESSION_SECURE` | false | true |

Jangan membuat file `.env` berisi password production di repository. Gunakan secret manager atau environment web server.

## 3. Deploy aplikasi

1. Jalankan test suite pada staging.
2. Backup database sebelum migration.
3. Deploy source code ke document root.
4. Jalankan migration yang berversi, bukan mengedit schema manual.
5. Pastikan user database hanya memiliki hak pada database aplikasi.
6. Verifikasi pemeriksaan kesehatan, login, input, laporan, dan PWA.
7. Aktifkan monitoring sebelum production.

## 4. Apache

Document root harus mengarah ke folder aplikasi. Folder `config`, `includes`, `partials`, dan `database` harus ditolak oleh web server.

Untuk PWA, file berikut harus dapat diakses publik:

```text
manifest.json
sw.js
offline.html
modules/gizi/index.html
assets/css/style.css
assets/js/app.js
assets/images/icon-192.png
assets/images/icon-512.png
```

## 5. Backup dan restore

- Backup harian minimal.
- Simpan minimal satu salinan di lokasi berbeda.
- Backup harus terenkripsi.
- Lakukan restore drill secara berkala.
- Catat RPO/RTO sebelum go-live.

Contoh restore manual:

```bash
mysql -u <user> -p <database> < backup.sql
```

## 6. Monitoring

Pantau:

- uptime dan response time;
- error log PHP dan web server;
- koneksi/query database;
- kapasitas disk dan certificate expiry;
- backup harian;
- audit log dan aktivitas admin.

## 7. Release dan rollback

- Gunakan commit atau release number yang jelas.
- Simpan artefak rilis sebelumnya.
- Uji migration pada staging.
- Siapkan kriteria rollback.
- Jangan melakukan edit database manual di production.
- Cache Service Worker harus memiliki versi baru ketika asset aplikasi berubah.

## 8. Blocker sebelum produksi

- Ganti `DEMO-IFELSE` dengan standar dan reference vector resmi.
- Dapatkan persetujuan tenaga kesehatan.
- Implementasikan password reset/MFA sesuai kebutuhan.
- Review privasi, persetujuan, retensi, dan permintaan pihak data.
- Jalankan security test dan UAT.
- Pastikan data demo tidak terbawa ke production.