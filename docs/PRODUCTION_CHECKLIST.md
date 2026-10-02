# Production Checklist

Checklist ini untuk menggantikan status **MVP akademik** menjadi pilot/production. Centang semua item dan simpan bukti validasinya.

## Domain dan klinis

- [ ] Standar antropometri resmi disetujui tenaga kesehatan.
- [ ] Tabel LMS/z-score dan batas usia tersedia.
- [ ] Reference vector dari standar resmi lulus.
- [ ] Metode panjang berbaring/tinggi berdiri tervalidasi.
- [ ] Label demo `DEMO-IFELSE` dihapus dari production.
- [ ] Alur rujukan dan verifikasi ditetapkan.

## Data dan privasi

- [ ] Tidak ada data sintetis yang ikut ke production.
- [ ] NIK/kontak hanya dikumpulkan jika diperlukan.
- [ ] Persetujuan wali tersedia dan dapat diaudit.
- [ ] Retention, export, correction, dan deletion policy disetujui.
- [ ] User database memakai least privilege.
- [ ] Backup dienkripsi dan restore drill berhasil.

## Security

- [ ] HTTPS aktif dan `SESSION_SECURE=true`.
- [ ] Password hash, session timeout, dan regenerasi ID aktif.
- [ ] CSRF, prepared statement, output escaping, dan RBAC diuji.
- [ ] Rate limit login tidak hanya mengandalkan session in-memory.
- [ ] Security header dan error display production dinonaktifkan.
- [ ] Audit log ditinjau dan tidak membocorkan data sensitif.

## PWA dan operasional

- [ ] Service Worker terdaftar pada HTTPS.
- [ ] Halaman PHP/API terautentikasi tidak masuk cache.
- [ ] Panduan gizi tersedia offline.
- [ ] `CACHE_NAME` dinaikkan setiap rilis asset.
- [ ] Logging, uptime alert, backup alert, dan certificate monitor aktif.
- [ ] Rollback release dan recovery plan diuji.

## UAT

- [ ] Kader menguji login, CRUD, timbangan, dan laporan.
- [ ] Supervisor menguji arsip dan review.
- [ ] Perangkat low-end dan jaringan buruk diuji.
- [ ] Tidak ada blocker P0/P1 terbuka.
- [ ] Go-live disetujui oleh pemilik produk, teknis, dan tenaga kesehatan.