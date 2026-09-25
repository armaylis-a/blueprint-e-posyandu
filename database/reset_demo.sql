-- =============================================================================
-- Reset Data Demo E-Posyandu
-- -----------------------------------------------------------------------------
-- PERINGATAN: Script ini MENGHAPUS seluruh data pada database MVP/demo.
-- Jangan pernah menjalankannya pada database yang berisi data nyata.
-- Setelah script ini, jalankan kembali seed_demo.sql.
-- =============================================================================

SET NAMES utf8mb4;
USE `e_posyandu_mvp`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `hasil_antropometri`;
TRUNCATE TABLE `timbangan_bulanan`;
TRUNCATE TABLE `consent_records`;
TRUNCATE TABLE `standar_antropometri`;
TRUNCATE TABLE `data_balita`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `posyandu`;
SET FOREIGN_KEY_CHECKS = 1;
