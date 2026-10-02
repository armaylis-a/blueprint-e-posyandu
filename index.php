<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_require();

$currentUser = auth_user() ?? [];
$monthBounds = report_month_bounds(date('Y-m'));
$counts = [
    'balita'            => 0,
    'timbangan'         => 0,
    'posyandu'          => 0,
    'month_measurements' => 0,
    'month_indication'  => 0,
    'month_normal'      => 0,
];
$recentMeasurements = [];
$databaseReady = true;

try {
    $countStatement = db()->prepare(
        'SELECT
            (SELECT COUNT(*) FROM data_balita
              WHERE id_posyandu = :posyandu_balita AND is_active = 1) AS balita,
            (SELECT COUNT(*)
               FROM timbangan_bulanan t
               INNER JOIN data_balita b ON b.id_balita = t.id_balita
              WHERE b.id_posyandu = :posyandu_timbangan
                AND t.deleted_at IS NULL) AS timbangan,
            (SELECT COUNT(*) FROM posyandu WHERE is_active = 1) AS posyandu,
            (SELECT COUNT(*)
               FROM timbangan_bulanan t
               INNER JOIN data_balita b ON b.id_balita = t.id_balita
              WHERE b.id_posyandu = :posyandu_month
                AND t.deleted_at IS NULL
                AND t.tgl_periksa BETWEEN :month_start AND :month_end) AS month_measurements,
            (SELECT COUNT(*)
               FROM timbangan_bulanan t
               INNER JOIN data_balita b ON b.id_balita = t.id_balita
               INNER JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan
              WHERE b.id_posyandu = :posyandu_indication
                AND t.deleted_at IS NULL
                AND t.tgl_periksa BETWEEN :indication_start AND :indication_end
                AND h.status_stunting = :indication_status) AS month_indication,
            (SELECT COUNT(*)
               FROM timbangan_bulanan t
               INNER JOIN data_balita b ON b.id_balita = t.id_balita
               INNER JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan
              WHERE b.id_posyandu = :posyandu_normal
                AND t.deleted_at IS NULL
                AND t.tgl_periksa BETWEEN :normal_start AND :normal_end
                AND h.status_stunting = :normal_status) AS month_normal'
    );
    $countStatement->execute([
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
    $counts = array_merge($counts, $countStatement->fetch() ?: []);

    $recentStatement = db()->prepare(
        'SELECT b.kode_balita, b.nama_balita, t.tgl_periksa,
                h.status_stunting, h.review_status
         FROM timbangan_bulanan t
         INNER JOIN data_balita b ON b.id_balita = t.id_balita
         LEFT JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan
         WHERE b.id_posyandu = :id_posyandu AND t.deleted_at IS NULL
         ORDER BY t.tgl_periksa DESC, b.nama_balita ASC
         LIMIT 5'
    );
    $recentStatement->execute(['id_posyandu' => current_posyandu_id()]);
    $recentMeasurements = $recentStatement->fetchAll();
} catch (PDOException $exception) {
    error_log('Gagal memuat dashboard: ' . $exception->getMessage());
    $databaseReady = false;
}

$pageTitle = 'Dashboard';
$showNavigation = true;
$bodyClass = 'dashboard-page';
require __DIR__ . '/partials/header.php';
?>

<section class="hero-card">
    <div>
        <p class="eyebrow">DASHBOARD · DATA SINTETIS</p>
        <h1>Selamat datang, <?= e($currentUser['nama_lengkap'] ?? 'Pengguna') ?>.</h1>
        <p class="muted">Autentikasi, CRUD balita, timbangan, dan laporan bulanan telah aktif untuk MVP.</p>
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
        Database belum dapat diakses. Periksa konfigurasi koneksi dan import schema/seed.
    </div>
<?php endif; ?>

<section class="stats-grid" aria-label="Ringkasan data demo">
    <article class="stat-card">
        <span class="stat-label">Balita aktif</span>
        <strong><?= e((string) ($counts['balita'] ?? 0)) ?></strong>
        <small>Data sintetis terdaftar</small>
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
        <small>Hasil simulasi</small>
    </article>
    <article class="stat-card">
        <span class="stat-label">Posyandu</span>
        <strong><?= e((string) ($counts['posyandu'] ?? 0)) ?></strong>
        <small>Unit layanan demo</small>
    </article>
</section>

<section class="content-grid">
    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">MODUL MVP</p>
                <h2>Pengembangan</h2>
            </div>
        </div>
        <div class="module-list">
            <div class="module-item">
                <span class="module-number">01</span>
                <div><strong>Autentikasi</strong><small>Selesai · session, CSRF, role demo</small></div>
                <span class="status status-done">Selesai</span>
            </div>
            <div class="module-item">
                <span class="module-number">02</span>
                <div><strong>CRUD Balita</strong><small>Selesai · arsip soft delete</small></div>
                <span class="status status-done">Selesai</span>
            </div>
            <div class="module-item">
                <span class="module-number">03</span>
                <div><strong>Timbangan & stunting</strong><small>Selesai · assessor demo IF-ELSE</small></div>
                <span class="status status-done">Selesai</span>
            </div>
            <div class="module-item">
                <span class="module-number">04</span>
                <div><strong>Laporan bulanan</strong><small>Selesai · filter periode dan cetak</small></div>
                <span class="status status-done">Selesai</span>
            </div>
            <div class="module-item">
                <span class="module-number">05</span>
                <div><strong>PWA & offline</strong><small>Informasi gizi read-only</small></div>
                <span class="status status-planned">Direncanakan</span>
            </div>
        </div>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">DATA TERBARU</p>
                <h2>Pengukuran Demo</h2>
            </div>
            <a class="text-action" href="<?= e(app_url('modules/timbangan/index.php')) ?>">Lihat semua</a>
        </div>
        <?php if ($recentMeasurements === []): ?>
            <p class="empty-state">Belum ada data pengukuran.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Balita</th>
                            <th>Periksa</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentMeasurements as $measurement): ?>
                        <tr>
                            <td>
                                <strong><?= e($measurement['kode_balita']) ?></strong><br>
                                <small><?= e($measurement['nama_balita']) ?></small>
                            </td>
                            <td><?= e(format_date_id((string) $measurement['tgl_periksa'])) ?></td>
                            <td>
                                <span class="status <?= ($measurement['review_status'] ?? '') === 'needs_review' ? 'status-review' : 'status-done' ?>">
                                    <?= e($measurement['status_stunting'] ?? '-') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>

<section class="notice-panel">
    <strong>Catatan akademik.</strong>
    Hasil `demo_indikasi_stunting` dan `demo_normal` bukan diagnosis klinis. Seed demo belum memiliki tabel LMS/z-score resmi.
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>