<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_require();

$currentUser = auth_user() ?? [];
$monthBounds = report_month_bounds(date('Y-m'));$counts = [
    'balita'             => 0,
    'timbangan'          => 0,
    'posyandu'           => 0,
    'month_measurements' => 0,
    'month_indication'   => 0,
    'month_normal'       => 0,
];
$recentMeasurements = [];$databaseReady = true;

try {
    $countStatement = db()->prepare( 'SELECT (SELECT COUNT(*) FROM data_balita WHERE id_posyandu = :posyandu_balita AND is_active = 1) AS balita, (SELECT COUNT(*) FROM timbangan_bulanan t INNER JOIN data_balita b ON b.id_balita = t.id_balita WHERE b.id_posyandu = :posyandu_timbangan AND t.deleted_at IS NULL) AS timbangan, (SELECT COUNT(*) FROM posyandu WHERE is_active = 1) AS posyandu, (SELECT COUNT(*) FROM timbangan_bulanan t INNER JOIN data_balita b ON b.id_balita = t.id_balita WHERE b.id_posyandu = :posyandu_month AND t.deleted_at IS NULL AND t.tgl_periksa BETWEEN :month_start AND :month_end) AS month_measurements, (SELECT COUNT(*) FROM timbangan_bulanan t INNER JOIN data_balita b ON b.id_balita = t.id_balita INNER JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan WHERE b.id_posyandu = :posyandu_indication AND t.deleted_at IS NULL AND t.tgl_periksa BETWEEN :indication_start AND :indication_end AND h.status_stunting = :indication_status) AS month_indication, (SELECT COUNT(*) FROM timbangan_bulanan t INNER JOIN data_balita b ON b.id_balita = t.id_balita INNER JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan WHERE b.id_posyandu = :posyandu_normal AND t.deleted_at IS NULL AND t.tgl_periksa BETWEEN :normal_start AND :normal_end AND h.status_stunting = :normal_status) AS month_normal' );$countStatement->execute([
        'posyandu_balita'     => current_posyandu_id(),
        'posyandu_timbangan'  => current_posyandu_id(),
        'posyandu_month'      => current_posyandu_id(),
        'month_start'         => $monthBounds['start'],
        'month_end'           => $monthBounds['end'],
        'posyandu_indication' => current_posyandu_id(),
        'indication_start'    => $monthBounds['start'],
        'indication_end'      => $monthBounds['end'],
        'indication_status'   => 'demo_indikasi_stunting',
        'posyandu_normal'     => current_posyandu_id(),
        'normal_start'        => $monthBounds['start'],
        'normal_end'          => $monthBounds['end'],
        'normal_status'       => 'demo_tidak_ada_indikasi',
    ]);
    $counts = array_merge($counts,$countStatement->fetch() ?: []);

    $recentStatement = db()->prepare( 'SELECT b.kode_balita, b.nama_balita, t.tgl_periksa, h.status_stunting, h.review_status FROM timbangan_bulanan t INNER JOIN data_balita b ON b.id_balita = t.id_balita LEFT JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan WHERE b.id_posyandu = :id_posyandu AND t.deleted_at IS NULL ORDER BY t.tgl_periksa DESC, b.nama_balita ASC LIMIT 5' );$recentStatement->execute(['id_posyandu' => current_posyandu_id()]);
    $recentMeasurements =$recentStatement->fetchAll();
} catch (PDOException $exception) {
    error_log('Gagal memuat dashboard: ' . $exception->getMessage());$databaseReady = false;
}

$pageTitle = 'Dashboard';$showNavigation = true;
$bodyClass = 'dashboard-page';
require __DIR__ . '/partials/header.php';

// Penggantian nama sapaan khusus tampilan visual menjadi "Kader Anggrek"
$namaTampil = str_replace('Kader Demo', 'Kader Anggrek', (string)($currentUser['nama_lengkap'] ?? 'Pengguna'));
?>

<section class="hero-card">
    <div>
        <p class="eyebrow">DASHBOARD UTAMA</p>
        <h1>Selamat datang, <?= e($namaTampil) ?>.</h1>
        <p class="muted">Sistem pencatatan balita, riwayat penimbangan, dan laporan bulanan e-Posyandu.</p>
        <div class="heading-actions">
            <a class="button button-light" href="<?= e(app_url('modules/balita/index.php')) ?>">Kelola Balita</a>
            <a class="button button-light" href="<?= e(app_url('modules/timbangan/form.php')) ?>">Input Timbangan</a>
            <a class="button button-light" href="<?= e(app_url('modules/laporan/index.php')) ?>">Buka Laporan</a>
        </div>
    </div>
    <div class="role-chip">
        <span>Peran</span>
        <strong><?= e($currentUser['role'] ?? '') ?></strong>
    </div>
</section>

<?php if (!$databaseReady): ?>
    <div class="alert alert-error" role="alert">
        Database belum dapat diakses. Periksa konfigurasi koneksi dan import schema.
    </div>
<?php endif; ?>

<section class="stats-grid" aria-label="Ringkasan data">
    <article class="stat-card">
        <span class="stat-label">Balita aktif</span>
        <strong><?= e((string) ($counts['balita'] ?? 0)) ?></strong>
        <small>Terdaftar</small>
    </article>
    <article class="stat-card">
        <span class="stat-label">Total timbangan</span>
        <strong><?= e((string) ($counts['timbangan'] ?? 0)) ?></strong>
        <small>Seluruh riwayat</small>
    </article>
    <article class="stat-card">
        <span class="stat-label">Timbangan bulan ini</span>
        <strong><?= e((string) ($counts['month_measurements'] ?? 0)) ?></strong>
        <small><?= e(report_month_label($monthBounds['month'])) ?></small>
    </article>
    <article class="stat-card">
        <span class="stat-label">Indikasi bulan ini</span>
        <strong><?= e((string) ($counts['month_indication'] ?? 0)) ?></strong>
        <small>Perlu ditinjau</small>
    </article>
    <article class="stat-card">
        <span class="stat-label">Normal bulan ini</span>
        <strong><?= e((string) ($counts['month_normal'] ?? 0)) ?></strong>
        <small>Bulan ini</small>
    </article>
    <article class="stat-card">
        <span class="stat