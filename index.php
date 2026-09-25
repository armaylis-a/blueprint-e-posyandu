<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_require();

$currentUser = auth_user() ?? [];
$counts = [
    'balita'    => 0,
    'timbangan' => 0,
    'posyandu'  => 0,
];
$recentMeasurements = [];
$databaseReady = true;

try {
    $countStatement = db()->query(
        'SELECT
            (SELECT COUNT(*) FROM data_balita WHERE is_active = 1) AS balita,
            (SELECT COUNT(*) FROM timbangan_bulanan WHERE deleted_at IS NULL) AS timbangan,
            (SELECT COUNT(*) FROM posyandu WHERE is_active = 1) AS posyandu'
    );
    $counts = array_merge($counts, $countStatement->fetch() ?: []);

    $recentStatement = db()->query(
        'SELECT kode_balita, nama_balita, tgl_periksa, status_stunting, review_status
         FROM v_demo_timbangan
         ORDER BY tgl_periksa DESC, kode_balita ASC
         LIMIT 5'
    );
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
        <p class="muted">Modul autentikasi MVP telah aktif. DATA berikutnya menggunakan data contoh fiktif.</p>
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
        <span class="stat-label">Balita demo</span>
        <strong><?= e((string) ($counts['balita'] ?? 0)) ?></strong>
        <small>Data sintetis terdaftar</small>
    </article>
    <article class="stat-card">
        <span class="stat-label">Timbangan</span>
        <strong><?= e((string) ($counts['timbangan'] ?? 0)) ?></strong>
        <small>Riwayat pengukuran</small>
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
                <p class="eyebrow">MODUL BERIKUTNYA</p>
                <h2>Pengembangan MVP</h2>
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
                <div><strong>CRUD Balita</strong><small>Belum dikerjakan</small></div>
                <span class="status status-next">Berikutnya</span>
            </div>
            <div class="module-item">
                <span class="module-number">03</span>
                <div><strong>Timbangan & stunting</strong><small>Simulasi demo, bukan diagnosis</small></div>
                <span class="status status-planned">Direncanakan</span>
            </div>
            <div class="module-item">
                <span class="module-number">04</span>
                <div><strong>Laporan & PWA</strong><small>Setelah core modules</small></div>
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
                                    <?= e($measurement['status_stunting']) ?>
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
    Hasil `demo_indikasi_stunting` dan `demo_normal` bukan diagnosis klinis.
    Seed demo belum memiliki tabel LMS/z-score resmi.
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
