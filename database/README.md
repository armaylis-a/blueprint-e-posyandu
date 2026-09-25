# Database MVP Akademik E-Posyandu

Folder ini berisi skema dan data tiruan untuk tahap **MVP akademik**. Tidak ada data anak, ibu, wali, NIK, nomor telepon, atau alamat nyata di dalam seed.

## Isi folder

- `schema.sql` — membuat database `e_posyandu_mvp` dan tabel utama.
- `seed_demo.sql` — mengisi akun, Posyandu, balita, timbangan, hasil demo, persetujuan sintetis, dan audit log.
- `reset_demo.sql` — menghapus seluruh isi database MVP dan digunakan sebelum memuat ulang seed.

## Cara import di XAMPP/phpMyAdmin

1. Start **Apache** dan **MySQL** dari XAMPP.
2. Buka `http://localhost/phpmyadmin`.
3. Import `database/schema.sql`.
4. Import `database/seed_demo.sql`.
5. Jalankan query verifikasi berikut:

```sql
USE e_posyandu_mvp;

SELECT * FROM v_demo_timbangan ORDER BY kode_balita;

SELECT username, role, is_active
FROM users
ORDER BY id_user;

SELECT status_stunting, COUNT(*) AS jumlah
FROM hasil_antropometri
GROUP BY status_stunting;
```

## Akun demo

| Username | Password | Peran |
|---|---|---|
| `kader.demo` | `DemoKader123!` | kader |
| `supervisor.demo` | `DemoSupervisor123!` | supervisor |

Password di atas hanya untuk demo lokal. Jangan menggunakan kredensial ini di server publik.

## Catatan penting tentang stunting

Seed `DEMO-IFELSE` hanya mensimulasikan ambang tinggi dari blueprint:

- 0–6 bulan: 60 cm;
- 7–12 bulan: 71 cm;
- 13–24 bulan: 80 cm;
- lebih dari 24 bulan: 88 cm.

Data ini **bukan standar klinis resmi** dan tidak boleh dipakai untuk keputusan kesehatan. Sebelum pilot/produksi, ganti dengan standar Kemenkes/WHO yang disetujui, tabel LMS/z-score, validasi metode ukur, serta tinjauan tenaga kesehatan.

## Aturan selama pengembangan

- Gunakan hanya data sintetis.
- Jangan memasukkan NIK, telepon, alamat, atau data individu nyata.
- Jangan menyimpan password plaintext.
- Gunakan prepared statement pada PHP.
- Hitung `umur_bulan` di server dari `tgl_lahir` dan `tgl_periksa`.
- Verifikasi `id_balita` terhadap Posyandu pengguna.
- Jangan menghapus riwayat pengukuran dengan cascade.
- Jalankan `reset_demo.sql` hanya pada database demo.

## Melewati tahap produksi

Sebelum memakai data nyata:

1. Ganti `DEMO-IFELSE` dengan standar antropometri resmi dan tabel referensi.
2. Validasi algoritma dengan tenaga kesehatan.
3. Tambahkan kontrol akses, persetujuan wali, retention, backup, monitoring, dan UAT.
4. Hapus atau arsipkan seluruh data sintetis sebelum migrasi.
5. Gunakan migration terpisah; jangan mengedit langsung database produksi.
