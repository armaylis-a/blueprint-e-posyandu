# Analisis Blueprint E-Posyandu hingga Tahap Produksi

**Sumber:** `60225063_Armaylis Arfa Blueprint PemWeb - C.pdf` (14 halaman)  
**Judul sumber:** Laporan Blueprint Arsitektur Perangkat Lunak Sistem Informasi E-Posyandu Berbasis Web-Mobile Menggunakan PWA  
**Teknik yang dianalisis:** PHP Native prosedural, MySQL/MariaDB, Bootstrap 5, JavaScript Native, Service Worker, dan Web App Manifest

> **Kesimpulan eksekutif:** Blueprint ini sudah cukup baik sebagai blueprint akademik atau prototipe MVP, tetapi **belum aman untuk langsung dipakai di produksi**. Risiko terbesar adalah logika deteksi stunting yang belum sesuai standar antropometri, tabel autentikasi yang belum ada, keamanan yang masih dasar, dan klaim offline yang hanya meng-cache halaman informasi.

---

## Keputusan Lingkup MVP Akademik

Keputusan ini berarti proyek dilanjutkan sebagai **MVP akademik**, bukan sistem produksi, selama masa pengembangan:

- seluruh data yang digunakan harus **tiruan/fiktif**;
- tidak memasukkan nama, NIK, nomor telepon, alamat, rekam medis, atau data pribadi anak/wali sebenarnya;
- gunakan kode anak generik, misalnya `BALITA-001`, dan tanggal contoh yang tidak merujuk pada individu nyata;
- sediakan `seed`/reset data agar demo dapat diulang;
- tampilkan label **Data Sintetis/Demo** pada halaman dan README;
- lingkungan pengembangan, pengujian, dan staging harus terpisah;
- kredensial, database, dan token yang digunakan tidak boleh berasal dari sistem nyata;
- data tiruan pada PWA hanya boleh digunakan untuk simulasi, bukan sebagai data operasional;
- sebelum pilot/produksi, data tiruan harus dihapus atau diarsipkan, lalu diganti dengan data yang telah mendapat persetujuan dan kontrol akses.

Logika stunting sederhana pada blueprint boleh dipertahankan hanya sebagai **simulasi algoritma IF-ELSE** untuk demonstrasi akademik, dengan label yang jelas bahwa hasilnya bukan diagnosis. Untuk kualitas akademik yang lebih baik, implementasi utama sebaiknya menggunakan perhitungan z-score/standar yang telah divalidasi.

### Status Implementasi Tahap 1

- [x] Skema database MVP: `database/schema.sql`
- [x] Data sintetis: `database/seed_demo.sql`
- [x] Script reset demo: `database/reset_demo.sql`
- [x] Panduan import: `database/README.md`
- [x] Data demo diverifikasi pada MariaDB 10.4
- [x] Autentikasi dan session login
- [x] CRUD balita sintetis
- [x] Input timbangan dan assessor demo
- [x] Dashboard ringkasan dan laporan bulanan/cetak
- [x] PWA read-only untuk panduan gizi offline
- [x] Test plan, validasi seed, deployment guide, dan production checklist
- [x] Ekspor CSV aman dan tampilan cetak

### Status Akhir

- **MVP akademik:** fitur yang disepakati selesai 100% untuk demo/akademik.
- **Production:** belum 100%; masih ada blocker standar klinis, UAT, backup, monitoring, dan review privasi.

---

## 1. Ringkasan Blueprint

### 1.1 Masalah yang diselesaikan

Blueprint menempati masalah yang relevan:

1. Buku KIA fisik mudah rusak, hilang, atau sulit direkap.
2. Kader harus menghitung dan mencocokkan tinggi/berat secara manual.
3. Deteksi stunting terlambat dan berpotensi salah karena human error.
4. Kader memerlukan informasi gizi/MPASI di lokasi dengan sinyal lemah.

### 1.2 Sasaran sistem

Sasaran yang dinyatakan dalam blueprint adalah:

- mengelola biodata balita dan rekam tumbuh kembang;
- menghasilkan label status gizi secara otomatis;
- menyediakan antarmuka web-mobile responsif;
- menyediakan informasi gizi yang dapat diakses secara offline melalui PWA.

### 1.3 Modul yang dirancang

- autentikasi/login kader;
- CRUD data balita;
- CRUD data timbangan bulanan;
- kalkulasi status stunting;
- informasi gizi/MPASI offline;
- laporan bulanan.

### 1.4 Alur utama

```text
Login -> Dashboard -> CRUD Balita
                   -> Input Timbangan -> Perhitungan stunting
                   -> Informasi Gizi Offline
                   -> Laporan Bulanan
```

### 1.5 Arsitektur yang dirancang

```text
Browser/PWA -> PHP Native Server -> MySQL/MariaDB
```

Arsitektur ini sederhana, murah, dan sesuai untuk prototipe atau pilot kecil. Namun, arsitektur file Prosedural dengan endpoint terpisah akan sulit dipelihara ketika jumlah fitur, pengguna, dan aturan bisnis bertambah.

---

## 2. Penilaian terhadap Capaian Blueprint

| Area | Penilaian | Catatan |
|---|---|---|
| Masalah dan ruang lingkup | Baik | Masalah nyata dan ruang lingkup masih terkendali. |
| Alur pengguna | Cukup | Alur utama tersedia, tetapi belum ada alur error, lupa kata sandi, dan sinkronisasi. |
| Rancangan database | Perlu diperbaiki | Hanya dua tabel; tabel pengguna, audit, dan master standar belum tersedia. |
| Logika stunting | Tidak siap produksi | Threshold tetap tidak memisahkan jenis kelamin, usia tepat, dan z-score. |
| Keamanan | Belum memadai | Session dan escaping saja belum cukup untuk produksi. |
| PWA | Cukup untuk MVP | Cocok untuk panduan read-only; belum mendukung pencatatan offline. |
| Pengujian | Sangat terbatas | Hanya dua test case logika. |
| Operasional | Belum ada | Belum ada deployment, monitoring, backup, disaster recovery, dan SOP. |

**Status yang disarankan:**

- **Blueprint akademik:** siap digunakan sebagai acuan coding dasar.
- **MVP pilot:** perlu penyederhanaan scope dan perbaikan keamanan.
- **Produksi:** belum lolos; harus melewati analisis domain, validasi klinis, dan UAT.

---

## 3. Analisis Kebutuhan Fungsional

| Kebutuhan | Ada di blueprint | Kekurangan untuk produksi | Prioritas |
|---|---:|---|---:|
| Login kader | Ya | Tidak ada tabel `users`, password hash, reset password, dan kebijakan sesi | P0 |
| CRUD balita | Ya | Belum ada pencarian, paginasi, penanganan duplikasi, kode internal, data wali, alamat, Posyandu, dan persetujuan | P0 |
| Input timbangan | Ya | Usia masih diinput manual; belum ada validasi, alat ukur, atau tanggal pemeriksaan | P0 |
| Deteksi stunting | Ya | Algoritma belum valid untuk keputusan klinis | P0 |
| Dashboard | Ada wireframe | Belum ada KPI, filter periode, status sinkronisasi, serta kondisi data kosong/error | P1 |
| Laporan bulanan | Ada | Hanya bulan berjalan; belum ada rentang tanggal, filter Posyandu, export, dan audit | P1 |
| Informasi gizi | Ada | Hanya konten statis; belum ada versi, pelokalan, dan alur tinjauan | P1 |
| PWA | Ada manifest dan Service Worker | Belum ada strategi pembaruan, halaman offline, dan sinkronisasi data | P0/P1 |
| Audit trail | Tidak ada | Perubahan data tidak dapat ditelusuri | P0 |
| Manajemen pengguna | Tidak ada | Tidak ada peran, akun nonaktif, reset, dan pembatasan akses | P0 |
| Backup/pemulihan | Tidak ada | Risiko kehilangan data tidak tertangani | P0 |

### Kebutuhan minimum untuk MVP produksi

1. Satu Posyandu atau lebih dengan data terisolasi per Posyandu.
2. Akun kader dengan autentikasi aman dan minimal dua peran: kader serta supervisor/admin.
3. Pendaftaran dan pencarian balita.
4. Input pengukuran dengan tanggal, usia turunan dari tanggal lahir, berat, tinggi, dan metode ukur.
5. Perhitungan hasil anthropometri memakai standar yang disetujui Kemenkes/WHO.
6. Simpan hasil mentah, z-score/hasil perhitungan, versi standar, dan siapa yang memverifikasi.
7. Laporan per periode dengan pencarian dan cetak.
8. Audit log untuk tambah, ubah, hapus, login, dan ekspor.
9. Backup harian serta uji restore.
10. UAT oleh kader sebelum go-live.

---

## 4. Temuan Kritis: Logika Deteksi Stunting

### 4.1 Masalah pada kode yang dirancang

Blueprint menggunakan batas tinggi tetap:

- usia 0–6 bulan: 60 cm;
- usia 7–12 bulan: 71 cm;
- usia 13–24 bulan: 80 cm;
- usia di atas 24 bulan: 88 cm.

 Selanjutnya, kode hanya melakukan:

```text
jika tinggi < batas_tinggi, status = Indikasi Stunting
jika tidak, status = Normal / Sehat
```

Pendekatan ini **tidak cukup untuk keputusan produksi**, karena:

1. Tidak membedakan jenis kelamin anak.
2. Tidak menggunakan usia tepat dalam hari/bulan.
3. Semua usia di atas 24 bulan memakai ambang yang sama, termasuk usia 3, 4, dan 5 tahun.
4. Tidak menggunakan z-score height-for-age atau standar pertumbuhan yang sesuai.
5. Tidak memperhitungkan metode pengukuran: panjang berbaring untuk anak di bawah dua tahun dan tinggi berdiri untuk anak dua tahun ke atas.
6. Tidak membedakan data yang ambigu, salah ukur, ekstrem, atau kosong dari status sehat.
7. Tidak menyimpan z-score, standar yang dipakai, dan versi algoritma.
8. Label `Normal / Sehat` terlalu mutlak; hasil sistem sebaiknya berupa hasil skrining dan rujukan klinis.
9. Tidak ada alur jika data suspect, hasil tidak valid, atau perlu pemeriksaan ulang.
10. Berat badan hanya disimpan tetapi tidak dipakai; padahal penilaian gizi dapat memerlukan BB/U, BB/TB, atau indeks lain sesuai standar.

### 4.2 Rekomendasi algoritma produksi

1. Simpan `tanggal_lahir` dan `tanggal_periksa` sebagai sumber usia.
2. Hitung usia secara otomatis dalam hari/bulan desimal.
3. Gunakan standar resmi yang telah disetujui, misalnya standar antropometri Kementerian Kesehatan RI dan/atau WHO Child Growth Standards, sesuai versi yang berlaku.
4. Hitung `height-for-age z-score`; contoh kategori stunting lazimnya menggunakan z-score di bawah -2 SD, tetapi cutoff final harus ditetapkan oleh tim kesehatan dan mengikuti standar yang ditetapkan. Jika sistem hanya menghitung TB/U, nyatakan bahwa hasilnya hanya skrining stunting—bukan status gizi keseluruhan—dan pertimbangkan penambahan BB/U serta BB/TB.
5. Untuk anak di bawah dua tahun gunakan standar panjang tubuh; untuk anak dua tahun ke atas gunakan standar tinggi badan dengan penyesuaian yang sesuai.
6. Simpan minimal:
   - `z_score`;
   - `classification`;
   - `reference_standard`;
   - `reference_version`;
   - `calculation_version`;
   - `review_status`;
   - `reviewed_by`;
   - waktu perhitungan.
7. Jika input di luar rentang wajar, beri status `perlu_pengukuran_ulang`, bukan otomatis `Normal`.
8. Sediakan tombol rujukan/tindak lanjut untuk kader, tanpa mengklaim sistem melakukan diagnosis.
9. Simpan tabel standar secara lokal untuk perhitungan offline, tetapi sinkronkan versi standar ke server secara terkontrol.

### 4.3 Rumusan fungsi yang lebih aman

```text
validasi_measurement()
  -> hitung_usia_dari_tanggal_lahir()
  -> tentukan_metode_pengukuran()
  -> validasi_fisik()
  -> hitung_indeks_antropometri()
  -> klasifikasi_screen()
  -> tandai_perlu_ditinjau bila data suspect
  -> simpan hasil + jejak audit
```

Kesimpulan: bagian deteksi stunting harus dianggap **prototipe logika IF-ELSE**, bukan spesifikasi final.

---

## 5. Analisis Database

### 5.1 Tabel yang ada

Blueprint hanya mendefinisikan:

- `data_balita(id_balita, nama_balita, jenis_kelamin, tgl_lahir, nama_ibu, tgl_daftar)`;
- `timbangan_bulanan(id_timbangan, id_balita, umur_bulan, berat_badan, tinggi_badan, status_gizi, tgl_periksa)`.

### 5.2 Masalah

1. Endpoint login ada, tetapi tidak ada tabel akun.
2. `nama_ibu` saja tidak cukup untuk model persetujuan dan identitas wali.
3. Tidak ada `posyandu_id`, sehingga multi-Posyandu tidak dapat diisolasi.
4. Tidak ada pencatatan siapa yang membuat atau mengubah data.
5. `ON DELETE CASCADE` dapat menghapus seluruh riwayat kesehatan saat data balita dihapus.
6. `status_gizi` disimpan sebagai teks hasil akhir tanpa z-score dan referensi.
7. Tidak ada unique key, indeks tanggal, version/audit, atau soft delete.
8. Tidak ada tanda sinkronisasi offline dan kunci idempotensi.
9. Tidak ada tabel konfigurasi standar/versi algoritma.

### 5.3 Model data minimum yang disarankan

```text
posyandu
  1 ─── * users
  1 ─── * children
  1 ─── * audit_logs

children
  1 ─── * measurements
  1 ─── * guardians
  1 ─── * consent_records

measurements
  1 ─── 1 growth_assessments
  1 ─── * audit_logs

users
  1 ─── * audit_logs
```

Contoh tabel inti:

- `posyandu`: identitas unit layanan dan wilayah.
- `users`: nama, email, `password_hash`, peran, status aktif, terakhir login.
- `children`: kode internal, nama, jenis kelamin, tanggal lahir, Posyandu, status aktif.
- `guardians`: nama, hubungan, kontak seperlunya.
- `consent_records`: dasar persetujuan, tujuan, waktu, status pencabutan.
- `measurements`: tanggal, berat, tinggi, metode, alat, petugas, sumber data.
- `growth_assessments`: z-score/hasil, standar, versi, flag, hasil tinjauan.
- `audit_logs`: aktor, aksi, objek, waktu, serta nilai sebelum/sesudah yang disanitasi.
- `sync_operations`: client UUID, status sinkronisasi, percobaan ulang, dan error.

Aturan penting:

- gunakan prepared statement dan validasi aplikasi;
- gunakan `ON DELETE RESTRICT` atau soft delete untuk riwayat kesehatan;
- simpan data mentah dan hasil perhitungan, bukan hanya status akhir;
- lindungi kolom sensitif dan batasi akses per Posyandu;
- tambahkan indeks pada `children(posyandu_id)`, `measurements(measured_at)`, dan `growth_assessments(status)`;
- gunakan tipe tanggal/waktu dan timezone yang konsisten;
- lakukan backup serta uji restore, bukan hanya menyimpan dump lokal.

---

## 6. Analisis Arsitektur dan Implementasi

### 6.1 Kelebihan stack yang dipilih

- biaya hosting rendah;
- mudah dijalankan pada lingkungan phpMyAdmin/XAMPP;
- cocok untuk tugas Pemrograman Web 1;
- Bootstrap 5 mempercepat responsive UI;
- PWA tidak memerlukan approval app store;
- MySQL mudah digunakan untuk CRUD dan laporan.

### 6.2 Kelemahan untuk produksi

- endpoint berupa file terpisah (`simpan.php`, `login.php`) mudah menyebabkan duplikasi logika;
- PHP prosedural tanpa lapisan service/repository sulit diuji;
- belum ada migrasi dan version-control skema;
- konfigurasi database berisiko masuk repository;
- tidak ada kontrak API, format error, paginasi, atau version control endpoint;
- raw SQL pada contoh masih menggabungkan input secara langsung;
- tidak ada transaksi antara penyimpanan pengukuran dan penilaian;
- tidak ada pencatatan log terstruktur atau ID permintaan.

### 6.3 Struktur yang lebih siap dipelihara

```text
app/
  Core/              # config, router, auth, middleware, error handler
  Domain/            # entity dan aturan growth
  Application/       # use case/service
  Infrastructure/    # MySQL repository, migrations, external service
  Http/              # controller, validation, response
public/
  index.php
  assets/
  manifest.json
  sw.js
config/
  ...
database/
  migrations/
  seeds/
tests/
  unit/
  integration/
  e2e/
```

Untuk coursework, migrasi gradual tetap dapat dilakukan tanpa langsung mengganti PHP Native. Untuk tim yang lebih besar atau sistem yang akan bertahan lebih lama, framework MVC dan ORM dapat mengurangi risiko, tetapi bukan syarat untuk membuat MVP yang benar.

### 6.4 API yang lebih tepat

| Endpoint | Fungsi | Catatan keamanan |
|---|---|---|
| `POST /api/v1/auth/login` | Login | Pembatas percobaan, respons generik, audit |
| `POST /api/v1/auth/logout` | Mengakhiri sesi | Invalidasi token/sesi |
| `GET/POST /api/v1/children` | Cari/tambah balita | Filter Posyandu |
| `GET/PATCH /api/v1/children/{id}` | Detail/ubah | Authorization per data |
| `POST /api/v1/measurements` | Input pengukuran | Validasi + transaksi |
| `GET /api/v1/measurements/{id}` | Detail hasil | Sertakan versi standar |
| `GET /api/v1/reports` | Rekap periode | Filter tanggal/Posyandu |
| `GET /api/v1/gizi` | Konten edukasi | Publik setelah review |
| `POST /api/v1/sync` | Kirim antrean offline | Idempotensi + validasi |

Semua respons sebaiknya memiliki schema, status HTTP yang konsisten, `request_id`, dan tidak mengembalikan detail error internal.

---

## 7. Analisis PWA dan Mode Offline

### 7.1 Apa yang sudah benar

- menggunakan `manifest.json` untuk kemampuan instalasi;
- menggunakan Service Worker;
- meng-cache halaman informasi gizi dan aset Bootstrap;
- Tujuannya adalah mendukung akses di wilayah minim sinyal.

### 7.2 Masalah pada Service Worker saat ini

Kode cache-first:

```javascript
self.addEventListener('fetch', (e) => {
  e.respondWith(caches.match(e.request).then((res) => res ||
    fetch(e.request)));
});
```

Jika diterapkan ke semua request, pola ini dapat:

- menyajikan HTML lama;
- menyimpan halaman yang sudah logout;
- menampilkan data kepada pengguna yang salah pada perangkat bersama;
- tidak memiliki fallback offline;
- tidak membersihkan cache versi lama;
- gagal memasang seluruh cache jika salah satu aset tidak ditemukan.

### 7.3 Rekomendasi minimal

- cache hanya aset statis dan halaman edukasi publik;
- jangan cache respons API, data sensitif, atau halaman terautentikasi;
- gunakan strategi network-first untuk halaman dinamis, atau `no-store` untuk data sensitif;
- tambahkan `OFFLINE_URL` dan halaman cadangan;
- gunakan nama cache versi, misalnya `eposyandu-v2`, dan hapus cache lama saat activate;
- tampilkan status online/offline dan proses pembaruan aplikasi;
- gunakan HTTPS atau localhost untuk Service Worker;
- lengkapi manifest dengan `scope`, `id`, `lang`, ikon maskable, dan tangkapan layar bila diperlukan.

### 7.4 Offline penuh membutuhkan desain tambahan

Jika kebutuhan kader adalah **menginput data saat offline**, cache saja tidak cukup. Diperlukan:

1. IndexedDB untuk draft dan antrean permintaan.
2. UUID/kunci idempotensi agar percobaan ulang tidak membuat data ganda.
3. Status `pending`, `synced`, `conflict`, atau `failed` per catatan.
4. Endpoint sync dan validasi ulang di server.
5. Strategi konflik; pendekatan `last write wins` tidak selalu aman untuk data kesehatan.
6. Indikator sinkron dan tombol coba lagi.
7. Logout menghapus cache lokal yang sensitif.
8. Kebijakan retensi untuk data yang belum tersinkron.
9. Uji pada perangkat Android kelas rendah dan jaringan yang tidak stabil.

Untuk MVP yang lebih aman, tunda dan dokumentasikan bahwa **offline hanya untuk baca informasi gizi**, sedangkan input data memerlukan koneksi.

---

## 8. Analisis Keamanan dan Privasi

### 8.1 Temuan pada rencana keamanan

Blueprint menyebut pemeriksaan sesi, `htmlspecialchars()`, dan `mysqli_real_escape_string()`. Ketiganya memang penting, tetapi belum cukup.

### 8.2 Rekomendasi minimum produksi

- gunakan prepared statement; `mysqli_real_escape_string()` bukan pengganti utama prepared statement;
- output encoding dengan `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`;
- `password_hash()` dengan Argon2id atau bcrypt;
- `session_regenerate_id(true)` setelah login;
- cookie `HttpOnly`, `Secure`, dan `SameSite`;
- batas waktu sesi dan logout yang benar-benar menghapus sesi di sisi server bila diperlukan;
- token CSRF untuk semua operasi yang mengubah data;
- pembatasan percobaan login dan alert/kunci sementara untuk percobaan gagal;
- validasi sisi server untuk semua kolom;
- otorisasi per ID dan per Posyandu, bukan hanya memeriksa sesi login;
- security header: CSP, HSTS, `X-Content-Type-Options`, dan kebijakan frame;
- pemindaian dependency dan rahasia;
- log aplikasi tanpa menyimpan password, token, atau data anak berlebihan.

### 8.3 Privasi data

Data kesehatan balita termasuk data sensitif. Aplikasi perlu perencanaan yang baik:

- persetujuan wali/pihak yang berwenang;
- tujuan pemrosesan yang jelas;
- pembatasan akses agar data hanya diakses oleh pihak yang berwenang;
- kebijakan retensi dan penghapusan;
- akses serta export log;
- backup terenkripsi;
- prosedur insiden kebocoran;
- tinjau kepatuhan terhadap UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi dan peraturan kesehatan yang berlaku.

Jangan memasukkan NIK atau data kontak yang tidak diperlukan oleh kebutuhan Posyandu. Gunakan kode internal anak sebagai identifier utama bila memungkinkan.

---

## 9. Rencana Pengujian

### 9.1 Kekuatan rencana pengujian saat ini

Kasus `TC-01` dan `TC-02` memang menguji batas tinggi untuk usia 12 bulan. Ini baru contoh kasus batas, belum menjadi rencana pengujian produksi.

### 9.2 Rencana pengujian yang diperlukan

| Level | Pengujian | Contoh kasus |
|---|---|---|
| Unit | Perhitungan usia | Tanggal lahir, tanggal periksa, tahun kabisatan, dan batas usia. |
| Unit | Z-score/indeks | Semua kelompok usia, L/P, batas −2 SD, input ekstrem. |
| Unit | Validasi | BB/TB negatif, nol, string, angka besar, kolom kosong. |
| Integrasi | Database | FK, duplikasi, rollback transaksi, konkurensi. |
| Integrasi | API | Autentikasi, peran, CSRF, status code, paginasi, schema respons. |
| Keamanan | Autentikasi | Password salah, serangan brute force, batas waktu sesi, bypass URL. |
| Keamanan | Injeksi | SQLi, XSS tersimpan/tercermin, IDOR, CSRF. |
| PWA | Offline | Instalasi, versi cache, pembaruan, fallback offline, sinkronisasi ulang. |
| Perangkat | Kompatibilitas | Android kelas rendah, iOS Safari, desktop, jaringan lambat. |
| UI | Kegunaan | Kondisi error/kosong/memuat, kontras, label, keyboard/screen reader. |
| Performa | Beban | 100/500 pengguna bersamaan, rencana query, p95 respons. |
| Operasional | Pemulihan | Backup harian, restore, rollback rilis, latihan pemulihan. |
| UAT | Operasional | Kader dan supervisor memakai skenario nyata. |

### 9.3 Kriteria penerimaan yang disarankan

- semua peran hanya dapat mengakses data sesuai kewenangannya;
- perhitungan dapat direproduksi dan memiliki z-score/versi standar;
- data yang suspect tidak otomatis dilabeli sehat;
- sinkronisasi offline tidak kehilangan atau menggandakan catatan;
- backup berhasil dipulihkan dalam latihan pemulihan;
- tidak ada temuan keamanan kritis/tinggi yang belum ditutup;
- laporan untuk periode yang dipilih sesuai hasil kueri;
- UAT kader selesai dan SOP tersedia sebelum peluncuran produksi.

Target performa harus ditetapkan bersama pemangku kepentingan; misalnya p95 halaman/API normal di bawah 2 detik pada koneksi target, dengan tolok ukur yang sesuai untuk jaringan Posyandu.

---

## 10. Tahapan Implementasi hingga Produksi

### Tahap 0 — Validasi domain dan lingkup

**Aktivitas:**
- tetapkan pengguna, data minimum, standar stunting, persetujuan, dan batas offline.

**Output:** SRS/fitur prioritas, kamus data awal, keputusan standar, dan model ancaman awal.

**Exit gate:** tim kesehatan menyetujui definisi hasil skrining dan batasan offline.

### Tahap 1 — Fondasi teknis

**Aktivitas:**

- repository dan kendali versi;
- environment lokal, staging, dan produksi;
- konfigurasi `.env` yang tidak di-commit;
- migrasi database;
- layout responsif;
- pencatatan log terstruktur dan halaman error.

**Output:** aplikasi dasar yang dapat dijalankan dengan bersih.

### Tahap 2 — Autentikasi dan otorisasi

**Aktivitas:**

- tabel pengguna/peran;
- password hash;
- penguatan sesi;
- middleware autentikasi/CSRF;
- audit login.

**Exit gate:** semua rute sensitif berada di balik autentikasi dan RBAC.

### Tahap 3 — Modul inti Posyandu

**Aktivitas:**

- CRUD balita dengan pencarian/paginasi;
- data wali/persetujuan minimum;
- input pengukuran tervalidasi;
- service penilaian;
- transaksi pengukuran + penilaian;
- dasbor dan laporan periode.

**Exit gate:** data dapat diinput, dihitung, ditinjau, dan dilacak.

### Tahap 4 — PWA

**Aktivitas:**

- manifest dan ikon;
- cache hanya konten publik;
- halaman cadangan offline;
- pembaruan Service Worker;
- jika diperlukan, IndexedDB/outbox/sinkronisasi.

**Exit gate:** uji instalasi, mode offline, pembaruan, dan sinkronisasi pada perangkat target.

### Tahap 5 — Verifikasi

**Aktivitas:**

- pengujian unit/integrasi/E2E;
- tinjauan keamanan;
- pengujian performa;
- pemeriksaan aksesibilitas;
- latihan backup/restore.

**Exit gate:** tidak ada hambatan P0/P1 yang terbuka.

### Tahap 6 — Pilot/UAT

**Aktivitas:**

- pilot pada 1–2 Posyandu;
- pelatihan kader;
- uji skenario nyata dan jaringan buruk;
- koreksi SOP dan antarmuka.

**Exit gate:** UAT selesai, latihan migrasi data, dan rencana rollback siap.

### Tahap 7 — Rilis produksi

**Aktivitas:**

- backup sebelum deploy;
- migrasi terkontrol;
- smoke test setelah deploy;
- observabilitas dan alert aktif;
- nomor versi rilis dan dokumentasi.

**Exit gate:** pemeriksaan kesehatan, login, input, laporan, backup, dan rollback semuanya terverifikasi.

### Tahap 8 — Operasional dan perbaikan

**Aktivitas:**

- monitor uptime/error/database;
- memperbarui dependency dan keamanan;
- tinjauan kualitas data;
- pelatihan ulang;
- mengembangkan fitur berdasarkan umpan balik.

Blueprint saat ini baru menjangkau tahap implementasi dasar. Tahap 5–8 perlu ditambahkan untuk disebut produksi.

---

## 11. Deployment dan Operasional Produksi

### Stack rekomendasi

- PHP 8.x dengan versi yang dipin dan didukung keamanannya;
- Nginx atau Apache sebagai web server;
- PHP-FPM bila Nginx digunakan;
- MySQL 8 atau MariaDB dengan charset, collation, backup, dan pengguna khusus aplikasi;
- HTTPS/TLS;
- CI/CD untuk pengujian, migrasi, build, dan deployment;
- pemantauan uptime, waktu respons, log error, database, disk, dan masa berlaku sertifikat;
- backup harian + pemulihan point-in-time;
- uji restore berkala;
- staging terpisah dari produksi.

### Strategi rilis

1. Gunakan semantic versioning atau nomor rilis yang jelas.
2. Migrasi harus kompatibel mundur bila memungkinkan.
3. Jalankan smoke test setelah deployment.
4. Simpan artefak rilis sebelumnya.
5. Siapkan rollback dan kriteria rollback.
6. Jangan melakukan edit database manual di produksi.
7. Pastikan ekspor data tidak membocorkan seluruh database ke client.

---

## 12. Checklist Go-Live

### Domain dan data

- [ ] Standar antropometri disetujui tim kesehatan.
- [ ] Z-score/hasil dan versi standar disimpan.
- [ ] tidak ada data yang dipaksa berstatus sehat.
- [ ] pengguna, peran, Posyandu, dan audit log tersedia.

### Keamanan dan privasi

- [ ] HTTPS aktif.
- [ ] password hash dan penguatan sesi aktif.
- [ ] CSRF, validasi, prepared statement, dan RBAC aktif.
- [ ] persetujuan dan kebijakan retensi disetujui.
- [ ] audit log telah ditinjau.

### PWA

- [ ] Manifest dan ikon valid.
- [ ] Hanya aset publik yang di-cache.
- [ ] fallback offline dan alur pembaruan diuji.
- [ ] lingkup offline CRUD ditentukan dan terdokumentasi.
- [ ] antrean sinkronisasi memiliki idempotensi dan retry.

### Operasional

- [ ] staging dan produksi terpisah.
- [ ] Backup berhasil dan restore diuji.
- [ ] pemantauan dan alert aktif.
- [ ] SOP, pelatihan, kontak dukungan, dan rollback tersedia.
- [ ] UAT oleh kader selesai.

---

## 13. Prioritas Temuan

### P0 — Harus selesai sebelum produksi

1. Ganti logika ambang dengan algoritme standar yang disetujui.
2. Tambahkan skema pengguna, peran, Posyandu, dan audit trail.
3. Terapkan prepared statement, CSRF, password hash, dan keamanan sesi.
4. Tentukan model hak akses data dan persetujuan wali.
5. Validasi data pengukuran dan simpan z-score/versi standar.
6. Tetapkan lingkup offline; jangan menjanjikan offline penuh tanpa IndexedDB/sinkronisasi.
7. Lengkapi rencana pengujian dan lakukan UAT.
8. Siapkan backup, restore, deployment, dan rollback.

### P1 — Sebaiknya sebelum pilot selesai

1. Dasbor KPI dan filter periode.
2. Laporan CSV/PDF dan tata letak cetak.
3. Pencarian/paginasi dan penanganan duplikasi.
4. Audit ekspor dan riwayat aktivitas.
5. Aksesibilitas dan dukungan perangkat kelas rendah.
6. SOP operasional serta pelatihan kader.

### P2 — Setelah MVP stabil

1. Integrasi dengan sistem kesehatan daerah/nasional.
2. Multi-tenancy dan dashboard lintas Posyandu.
3. Notifikasi dan pengingat pemeriksaan.
4. Aplikasi native atau integrasi dengan sistem lain jika benar-benar diperlukan oleh scope.
5. Dasbor analitik dan kualitas data.

---

## 14. Putusan Akhir

### Yang sudah baik

- masalah dan kebutuhan nyata;
- lingkup modular dan masuk akal untuk tugas web;
- alur login sampai laporan sudah digambarkan;
- UML, ERD, SQL, API lokal, PWA, rencana keamanan, dan rencana pengujian sudah disiapkan;
- stack mudah dipelajari dan berbiaya rendah.

### Yang belum boleh dianggap final

- `IF-ELSE` stunting berbasis ambang tinggi tetap;
- skema login yang tidak ada;
- ketergantungan pada `mysqli_real_escape_string()` sebagai keamanan utama;
- Service Worker cache-first untuk seluruh permintaan;
- asumsi bahwa satu peran selalu aman;
- dua kasus uji sebagai bukti kesiapan;
- tidak adanya deployment, pemantauan, backup, pemulihan, dan SOP.

### Rekomendasi keputusan

> **Status blueprint: Layak sebagai rancangan akademik dan MVP, belum layak sebagai spesifikasi produksi final.**

Langkah berikutnya yang paling penting adalah **validasi klinis algoritma stunting dan redesign database/autentikasi**, kemudian baru membangun modul inti, PWA, pengujian, pilot, dan peluncuran produksi.

---

## Lampiran: Draft Kriteria Penerimaan Produksi

Sistem dapat dinyatakan siap produksi bila:

1. Pengguna dapat login, logout, dan sesi berakhir dengan aman.
2. Pengguna hanya dapat mengakses data sesuai peran dan Posyandu.
3. Balita dapat didaftarkan, dicari, dan ditautkan ke pengukuran yang valid.
4. Usia dihitung dari tanggal lahir dan tanggal periksa.
5. Hasil skrining memakai standar, z-score, dan versi algoritma yang tercatat.
6. Data suspect menghasilkan perintah pengukuran ulang, bukan status sehat.
7. Laporan dapat difilter berdasarkan periode dan memiliki jejak audit.
8. PWA dapat dipasang, memperbarui cache, dan menampilkan fallback offline.
9. Data offline tidak hilang, tidak duplikat, dan memiliki status sinkronisasi.
10. Backup dan restore telah diuji.
11. UAT selesai dan tidak ada temuan keamanan kritis/tinggi.
12. SOP, pelatihan, dukungan, monitoring, dan rollback tersedia.
