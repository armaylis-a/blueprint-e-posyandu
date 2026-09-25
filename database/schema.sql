-- =============================================================================
-- E-Posyandu MVP Akademik - Skema Database
-- -----------------------------------------------------------------------------
-- Hanya untuk pengembangan/demo dengan DATA SINTETIS.
-- Tidak boleh digunakan untuk data anak/wali nyata tanpa review keamanan,
-- persetujuan, migrasi, dan prosedur operasional yang sesuai.
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

CREATE DATABASE IF NOT EXISTS `e_posyandu_mvp`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `e_posyandu_mvp`;

-- Catatan:
-- 1. Script ini aman dijalankan ulang untuk membuat tabel yang belum ada.
-- 2. Perubahan skema berikutnya sebaiknya dibuat sebagai migration terpisah.
-- 3. Tidak ada kolom NIK, nomor telepon, atau alamat pada MVP/demo.
-- 4. Foreign key menggunakan RESTRICT agar riwayat kesehatan tidak terhapus
--    ketika data induk balita dihapus.

CREATE TABLE IF NOT EXISTS `posyandu` (
  `id_posyandu` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_posyandu` VARCHAR(30) NOT NULL,
  `nama_posyandu` VARCHAR(150) NOT NULL,
  `wilayah` VARCHAR(150) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_posyandu`),
  UNIQUE KEY `uq_posyandu_kode` (`kode_posyandu`),
  CONSTRAINT `chk_posyandu_active` CHECK (`is_active` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_posyandu` SMALLINT UNSIGNED NOT NULL,
  `username` VARCHAR(60) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `role` ENUM('kader', 'supervisor', 'admin') NOT NULL DEFAULT 'kader',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_posyandu` (`id_posyandu`),
  CONSTRAINT `fk_users_posyandu`
    FOREIGN KEY (`id_posyandu`) REFERENCES `posyandu` (`id_posyandu`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `chk_users_active` CHECK (`is_active` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `data_balita` (
  `id_balita` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_posyandu` SMALLINT UNSIGNED NOT NULL,
  `kode_balita` VARCHAR(40) NOT NULL,
  `nama_balita` VARCHAR(100) NOT NULL,
  `jenis_kelamin` ENUM('L', 'P') NOT NULL,
  `tgl_lahir` DATE NOT NULL,
  `nama_wali` VARCHAR(100) NOT NULL,
  `tgl_daftar` DATE NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by_user_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_balita`),
  UNIQUE KEY `uq_balita_kode` (`kode_balita`),
  KEY `idx_balita_posyandu` (`id_posyandu`),
  KEY `idx_balita_tgl_lahir` (`tgl_lahir`),
  KEY `idx_balita_created_by` (`created_by_user_id`),
  CONSTRAINT `fk_balita_posyandu`
    FOREIGN KEY (`id_posyandu`) REFERENCES `posyandu` (`id_posyandu`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_balita_created_by`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id_user`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `chk_balita_active` CHECK (`is_active` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `consent_records` (
  `id_consent` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_balita` INT UNSIGNED NOT NULL,
  `status_persetujuan` ENUM('not_recorded', 'granted', 'withdrawn') NOT NULL DEFAULT 'not_recorded',
  `tanggal_persetujuan` DATE NULL,
  `catatan` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_consent`),
  KEY `idx_consent_balita` (`id_balita`),
  CONSTRAINT `fk_consent_balita`
    FOREIGN KEY (`id_balita`) REFERENCES `data_balita` (`id_balita`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `chk_consent_date`
    CHECK (`tanggal_persetujuan` IS NULL OR `tanggal_persetujuan` >= '2000-01-01')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `standar_antropometri` (
  `id_standar` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_standar` VARCHAR(50) NOT NULL,
  `versi_standar` VARCHAR(30) NOT NULL,
  `nama_standar` VARCHAR(150) NOT NULL,
  `jenis_kelamin` ENUM('L', 'P', 'ALL') NOT NULL DEFAULT 'ALL',
  `metode_pengukuran` VARCHAR(50) NOT NULL,
  `umur_min_bulan` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `umur_max_bulan` DECIMAL(5,2) NOT NULL DEFAULT 60.00,
  `z_score_threshold` DECIMAL(5,2) NULL,
  `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
  `catatan` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_standar`),
  UNIQUE KEY `uq_standar_segment` (`kode_standar`, `versi_standar`, `jenis_kelamin`, `umur_min_bulan`, `umur_max_bulan`),
  CONSTRAINT `chk_standard_age`
    CHECK (`umur_min_bulan` >= 0 AND `umur_max_bulan` <= 60 AND `umur_min_bulan` <= `umur_max_bulan`),
  CONSTRAINT `chk_standard_demo` CHECK (`is_demo` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `timbangan_bulanan` (
  `id_timbangan` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_balita` INT UNSIGNED NOT NULL,
  `tgl_periksa` DATE NOT NULL,
  -- Harus dihitung di server dari tgl_lahir dan tgl_periksa.
  -- Jangan mempercayai umur yang dikirimkan oleh form/browser.
  `umur_bulan` DECIMAL(5,2) NOT NULL,
  `berat_badan_kg` DECIMAL(5,2) NOT NULL,
  `tinggi_badan_cm` DECIMAL(5,2) NOT NULL,
  `jenis_pengukuran` ENUM('panjang_baring', 'tinggi_berdiri') NOT NULL,
  `sumber_data` ENUM('demo', 'web', 'offline') NOT NULL DEFAULT 'demo',
  `catatan` VARCHAR(255) NULL,
  `created_by_user_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id_timbangan`),
  KEY `idx_timbangan_balita_date` (`id_balita`, `tgl_periksa`),
  KEY `idx_timbangan_tanggal` (`tgl_periksa`),
  KEY `idx_timbangan_created_by` (`created_by_user_id`),
  KEY `idx_timbangan_deleted` (`deleted_at`),
  CONSTRAINT `fk_timbangan_balita`
    FOREIGN KEY (`id_balita`) REFERENCES `data_balita` (`id_balita`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_timbangan_created_by`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id_user`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `chk_timbangan_age`
    CHECK (`umur_bulan` >= 0 AND `umur_bulan` <= 60),
  CONSTRAINT `chk_timbangan_weight`
    CHECK (`berat_badan_kg` > 0 AND `berat_badan_kg` <= 200),
  CONSTRAINT `chk_timbangan_height`
    CHECK (`tinggi_badan_cm` > 0 AND `tinggi_badan_cm` <= 200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hasil_antropometri` (
  `id_hasil` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_timbangan` BIGINT UNSIGNED NOT NULL,
  `id_standar` INT UNSIGNED NOT NULL,
  `tb_u_zscore` DECIMAL(6,3) NULL,
  `bb_u_zscore` DECIMAL(6,3) NULL,
  `bb_tb_zscore` DECIMAL(6,3) NULL,
  `status_stunting` VARCHAR(40) NOT NULL,
  `status_gizi` VARCHAR(40) NOT NULL,
  `perlu_tinjauan` TINYINT(1) NOT NULL DEFAULT 0,
  `review_status` ENUM('pending', 'verified', 'needs_review') NOT NULL DEFAULT 'pending',
  `catatan` VARCHAR(255) NULL,
  `reviewed_by_user_id` INT UNSIGNED NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_hasil`),
  UNIQUE KEY `uq_hasil_timbangan` (`id_timbangan`),
  KEY `idx_hasil_status` (`status_stunting`, `status_gizi`),
  KEY `idx_hasil_standard` (`id_standar`),
  KEY `idx_hasil_reviewed_by` (`reviewed_by_user_id`),
  CONSTRAINT `fk_hasil_timbangan`
    FOREIGN KEY (`id_timbangan`) REFERENCES `timbangan_bulanan` (`id_timbangan`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_hasil_standard`
    FOREIGN KEY (`id_standar`) REFERENCES `standar_antropometri` (`id_standar`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_hasil_reviewed_by`
    FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id_user`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `chk_hasil_review` CHECK (`perlu_tinjauan` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id_audit` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_posyandu` SMALLINT UNSIGNED NOT NULL,
  `id_user` INT UNSIGNED NULL,
  `action` VARCHAR(50) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` VARCHAR(64) NULL,
  `detail` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_audit`),
  KEY `idx_audit_posyandu_time` (`id_posyandu`, `created_at`),
  KEY `idx_audit_user_time` (`id_user`, `created_at`),
  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
  CONSTRAINT `fk_audit_posyandu`
    FOREIGN KEY (`id_posyandu`) REFERENCES `posyandu` (`id_posyandu`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_audit_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Query laporan produksi sebaiknya menggunakan rentang tanggal, bukan hanya bulan.

-- Tampilan ringkas untuk kebutuhan demo. Nilai z-score demo sengaja NULL
-- karena tabel LMS/WHO resmi belum dimasukkan pada MVP akademik.
CREATE OR REPLACE VIEW `v_demo_timbangan` AS
SELECT
  b.kode_balita,
  b.nama_balita,
  b.jenis_kelamin,
  b.tgl_lahir,
  t.tgl_periksa,
  t.umur_bulan,
  t.berat_badan_kg,
  t.tinggi_badan_cm,
  t.jenis_pengukuran,
  h.status_stunting,
  h.status_gizi,
  h.perlu_tinjauan,
  h.review_status
FROM `timbangan_bulanan` t
INNER JOIN `data_balita` b ON b.id_balita = t.id_balita
INNER JOIN `hasil_antropometri` h ON h.id_timbangan = t.id_timbangan
WHERE t.deleted_at IS NULL;

-- Catatan implementasi aplikasi:
-- - SELECT/INSERT/SELECT/UPDATE harus menggunakan prepared statement.
-- - umur_bulan harus dihitung dari tgl_lahir dan tgl_periksa di server.
-- - id_balita harus diverifikasi terhadap id_posyandu milik pengguna.
-- - status_gizi pada MVP hanya demonstrasi dan bukan diagnosis klinis.
