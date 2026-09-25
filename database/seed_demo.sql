-- =============================================================================
-- E-Posyandu MVP Akademik - Data Demo Sintetis
-- -----------------------------------------------------------------------------
-- Jalankan setelah database/schema.sql.
-- Seluruh data di bawah ini fiktif dan hanya untuk demo/akademik.
-- Jangan mengganti data ini dengan data anak atau wali nyata tanpa prosedur resmi.
-- =============================================================================

SET NAMES utf8mb4;
USE `e_posyandu_mvp`;

-- Posyandu demo
INSERT IGNORE INTO `posyandu`
  (`id_posyandu`, `kode_posyandu`, `nama_posyandu`, `wilayah`, `is_active`)
VALUES
  (1, 'POSYANDU-DEMO-01', 'Posyandu Demo 01', 'Wilayah Demo', 1);

-- Password demo (hashed, bukan plaintext):
-- kader.demo      / DemoKader123!
-- supervisor.demo / DemoSupervisor123!
INSERT IGNORE INTO `users`
  (`id_user`, `id_posyandu`, `username`, `password_hash`, `nama_lengkap`, `role`, `is_active`)
VALUES
  (1, 1, 'kader.demo',
   '$2y$10$Z4NrC0GhmobTnB3wKrDKBO5.CxtiArM/J3RQstOneJfCWkvHUrW0S',
   'Kader Demo', 'kader', 1),
  (2, 1, 'supervisor.demo',
   '$2y$10$WdL19CNtEvQH5tDe/IgvLOXTFLQUTQz0METjIn02Huj819a06yv5m',
   'Supervisor Demo', 'supervisor', 1);

-- Standar demo. Ini BUKAN standar klinis resmi.
-- Tidak ada tabel LMS/Z-score WHO/Kemenkes pada seed demo ini.
INSERT IGNORE INTO `standar_antropometri`
  (`id_standar`, `kode_standar`, `versi_standar`, `nama_standar`, `jenis_kelamin`,
   `metode_pengukuran`, `umur_min_bulan`, `umur_max_bulan`, `z_score_threshold`,
   `is_demo`, `catatan`)
VALUES
  (1, 'DEMO-IFELSE', '1.0', 'Demo threshold IF-ELSE (bukan klinis)', 'ALL',
   'DEMO_IFELSE', 0.00, 60.00, NULL, 1,
   'Hanya simulasi akademik. Ganti dengan standar resmi dan tabel LMS sebelum pilot/produksi.');

-- Balita demo. Tidak ada NIK, telepon, alamat, atau data individu nyata.
INSERT IGNORE INTO `data_balita`
  (`id_balita`, `id_posyandu`, `kode_balita`, `nama_balita`, `jenis_kelamin`,
   `tgl_lahir`, `nama_wali`, `tgl_daftar`, `is_active`, `created_by_user_id`)
VALUES
  (1, 1, 'BALITA-DEMO-001', 'Anak Demo 001', 'L', '2023-01-15', 'Wali Demo 001', '2023-01-20', 1, 1),
  (2, 1, 'BALITA-DEMO-002', 'Anak Demo 002', 'P', '2023-07-20', 'Wali Demo 002', '2023-07-25', 1, 1),
  (3, 1, 'BALITA-DEMO-003', 'Anak Demo 003', 'L', '2024-01-10', 'Wali Demo 003', '2024-01-15', 1, 1),
  (4, 1, 'BALITA-DEMO-004', 'Anak Demo 004', 'P', '2022-01-20', 'Wali Demo 004', '2022-01-25', 1, 1),
  (5, 1, 'BALITA-DEMO-005', 'Anak Demo 005', 'L', '2024-07-01', 'Wali Demo 005', '2024-07-05', 1, 1),
  (6, 1, 'BALITA-DEMO-006', 'Anak Demo 006', 'P', '2023-03-12', 'Wali Demo 006', '2023-03-15', 1, 1),
  (7, 1, 'BALITA-DEMO-007', 'Anak Demo 007', 'L', '2024-12-12', 'Wali Demo 007', '2024-12-15', 1, 1),
  (8, 1, 'BALITA-DEMO-008', 'Anak Demo 008', 'P', '2022-07-08', 'Wali Demo 008', '2022-07-12', 1, 1);

-- Persetujuan demo untuk kebutuhan privasi/alur data.
INSERT IGNORE INTO `consent_records`
  (`id_consent`, `id_balita`, `status_persetujuan`, `tanggal_persetujuan`, `catatan`)
VALUES
  (1, 1, 'granted', '2023-01-20', 'Persetujuan sintetis untuk demo akademik.'),
  (2, 2, 'granted', '2023-07-25', 'Persetujuan sintetis untuk demo akademik.'),
  (3, 3, 'granted', '2024-01-15', 'Persetujuan sintetis untuk demo akademik.'),
  (4, 4, 'granted', '2022-01-25', 'Persetujuan sintetis untuk demo akademik.'),
  (5, 5, 'granted', '2024-07-05', 'Persetujuan sintetis untuk demo akademik.'),
  (6, 6, 'granted', '2023-03-15', 'Persetujuan sintetis untuk demo akademik.'),
  (7, 7, 'granted', '2024-12-15', 'Persetujuan sintetis untuk demo akademik.'),
  (8, 8, 'granted', '2022-07-12', 'Persetujuan sintetis untuk demo akademik.');

-- Timbangan sintetis. umur_bulan dihitung dari tanggal lahir + tanggal periksa
-- dan harus dihitung ulang di server saat aplikasi digunakan.
INSERT IGNORE INTO `timbangan_bulanan`
  (`id_timbangan`, `id_balita`, `tgl_periksa`, `umur_bulan`, `berat_badan_kg`,
   `tinggi_badan_cm`, `jenis_pengukuran`, `sumber_data`, `catatan`, `created_by_user_id`)
VALUES
  (1, 1, '2025-01-15', 24.00, 11.00, 80.00, 'tinggi_berdiri', 'demo', 'Data demo; perlu tinjauan.', 1),
  (2, 2, '2024-07-20', 12.00,  8.00, 68.50, 'panjang_baring', 'demo', 'Data demo; perlu tinjauan.', 1),
  (3, 3, '2024-07-10',  6.00,  6.00, 58.00, 'panjang_baring', 'demo', 'Data demo; perlu tinjauan.', 1),
  (4, 4, '2025-01-20', 36.00, 13.00, 90.00, 'tinggi_berdiri', 'demo', 'Data demo; perlu tinjauan.', 1),
  (5, 5, '2025-07-01', 12.00,  8.50, 73.00, 'panjang_baring', 'demo', 'Data demo; perlu tinjauan.', 1),
  (6, 6, '2025-03-12', 24.00, 10.50, 78.00, 'tinggi_berdiri', 'demo', 'Data demo; perlu tinjauan.', 1),
  (7, 7, '2025-06-12',  6.00,  6.80, 61.00, 'panjang_baring', 'demo', 'Data demo; perlu tinjauan.', 1),
  (8, 8, '2025-07-08', 36.00, 12.00, 86.00, 'tinggi_berdiri', 'demo', 'Data demo; perlu tinjauan.', 1);

-- Hasil demo. Status ini sengaja memakai threshold demo, bukan z-score klinis.
INSERT IGNORE INTO `hasil_antropometri`
  (`id_timbangan`, `id_standar`, `tb_u_zscore`, `bb_u_zscore`, `bb_tb_zscore`,
   `status_stunting`, `status_gizi`, `perlu_tinjauan`, `review_status`, `catatan`)
VALUES
  (1, 1, NULL, NULL, NULL, 'demo_tidak_ada_indikasi', 'demo_normal', 0, 'pending',
   'Contoh hasil simulasi; bukan diagnosis.'),
  (2, 1, NULL, NULL, NULL, 'demo_indikasi_stunting', 'demo_perlu_evaluasi', 1, 'needs_review',
   'Contoh hasil simulasi; bukan diagnosis.'),
  (3, 1, NULL, NULL, NULL, 'demo_indikasi_stunting', 'demo_perlu_evaluasi', 1, 'needs_review',
   'Contoh hasil simulasi; bukan diagnosis.'),
  (4, 1, NULL, NULL, NULL, 'demo_tidak_ada_indikasi', 'demo_normal', 0, 'pending',
   'Contoh hasil simulasi; bukan diagnosis.'),
  (5, 1, NULL, NULL, NULL, 'demo_tidak_ada_indikasi', 'demo_normal', 0, 'pending',
   'Contoh hasil simulasi; bukan diagnosis.'),
  (6, 1, NULL, NULL, NULL, 'demo_indikasi_stunting', 'demo_perlu_evaluasi', 1, 'needs_review',
   'Contoh hasil simulasi; bukan diagnosis.'),
  (7, 1, NULL, NULL, NULL, 'demo_tidak_ada_indikasi', 'demo_normal', 0, 'pending',
   'Contoh hasil simulasi; bukan diagnosis.'),
  (8, 1, NULL, NULL, NULL, 'demo_indikasi_stunting', 'demo_perlu_evaluasi', 1, 'needs_review',
   'Contoh hasil simulasi; bukan diagnosis.');

-- Contoh audit log untuk mendemonstrasikan jejak perubahan.
INSERT IGNORE INTO `audit_logs`
  (`id_audit`, `id_posyandu`, `id_user`, `action`, `entity_type`, `entity_id`, `detail`)
VALUES
  (1, 1, 1, 'seed', 'posyandu', '1', 'Data sintetis MVP dimuat.'),
  (2, 1, 1, 'seed', 'balita', '1', 'Data balita sintetis dibuat.'),
  (3, 1, 1, 'seed', 'timbangan', '2', 'Data timbangan sintetis dibuat.');

-- Verifikasi cepat setelah import:
-- SELECT * FROM v_demo_timbangan ORDER BY kode_balita;
-- SELECT username, role, is_active FROM users;
-- SELECT status_stunting, COUNT(*) AS jumlah FROM hasil_antropometri GROUP BY status_stunting;
