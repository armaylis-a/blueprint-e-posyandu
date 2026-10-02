# Test Plan MVP Akademik

## Automated validation

Pada PowerShell, jalankan dari root project:

```powershell
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3306'
$env:DB_NAME = 'e_posyandu_mvp'
$env:DB_USER = 'root'
$env:DB_PASSWORD = ''
& 'C:\xampp\php\php.exe' tests/validate_mvp.php
```

Script hanya memeriksa koneksi, seed demo, hash password, standar demo, dan integritas relasi. Hasil `PASS` berarti data demo siap untuk smoke test antarmuka.

## Manual smoke test

| ID | Skenario | Expected |
|---|---|---|
| AUTH-01 | Login username/password benar | Masuk ke dashboard |
| AUTH-02 | Password salah | Pesan generik, tidak membuka dashboard |
| AUTH-03 | Membuka `index.php` tanpa session | Redirect ke login |
| AUTH-04 | Logout | Session berakhir, kembali ke login |
| AUTH-05 | Kirim form tanpa CSRF | Ditolak |
| BAL-01 | Tambah kode balita unik | Data tersimpan dan muncul di detail |
| BAL-02 | Tambah kode duplikat | Ditolak dengan pesan validasi |
| BAL-03 | Edit data | Perubahan tersimpan |
| BAL-04 | Arsipkan sebagai kader | Ditolak karena RBAC |
| BAL-05 | Arsipkan sebagai supervisor | `is_active=0`, riwayat tetap ada |
| TIM-01 | Input tanggal sebelum lahir | Ditolak |
| TIM-02 | Input tanggal masa depan | Ditolak |
| TIM-03 | Input usia di atas 60 bulan | Ditolak |
| TIM-04 | Input berat/tinggi valid | Assessment demo tersimpan |
| TIM-05 | Input tanggal sama untuk balita yang sama | Ditolak |
| TIM-06 | Height di bawah threshold demo | Status indikasi demo |
| TIM-07 | Height di atas atau sama dengan threshold demo | Status normal demo |
| RPT-01 | Laporan bulan dengan data | Ringkasan dan tabel sesuai periode |
| RPT-02 | Laporan bulan tanpa data | Empty state |
| RPT-03 | Halaman cetak | Layout print dan tombol browser print |
| PWA-01 | Buka aplikasi sekali online | Service Worker terdaftar |
| PWA-02 | Buka panduan gizi offline | Konten tampil dari cache |
| PWA-03 | Buka halaman PHP offline | Data tidak diekspos, tampil fallback offline |

## Catatan

- Semua test wajib memakai data sintetis.
- Assessor `DEMO-IFELSE` bukan standar klinis.
- Sebelum pilot, test domain harus diganti dengan reference vector WHO/Kemenkes dan disetujui tenaga kesehatan.
- Sebelum production, jalankan backup restore drill dan security review.