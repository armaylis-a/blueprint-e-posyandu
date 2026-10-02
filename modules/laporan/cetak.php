<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$month = request_string($_GET, 'bulan', 7);
$report = null;

try {
    $report = fetch_monthly_report($month);
} catch (PDOException $exception) {
    error_log('Gagal memuat halaman cetak laporan: ' . $exception->getMessage());
}

$pageTitle = 'Cetak Laporan Bulanan';
$showNavigation = false;
$bodyClass = 'print-page';
require __DIR__ . '/../../partials/header.php';
?>

<section class="print-actions">
    <a class="button button-secondary" href="<?= e(app_url('modules/laporan/index.php?bulan=' . urlencode($report['month'] ?? date('Y-m')))) ?>">Kembali</a>
    <button class="button button-primary" type="button" data-print>Cetak halaman</button>
</section>

<?php if ($report === null): ?>
    <div class="alert alert-error" role="alert">Laporan tidak dapat dimuat.</div>
<?php else: ?>
    <section class="print-document">
        <header class="print-document-header">
            <div>
                <p class="eyebrow">LAPORAN POSYANDU · DATA SINTETIS</p>
                <h1>Laporan Bulanan</h1>
                <p><?= e($report['label']) ?> · <?= e(format_date_id($report['start'])) ?> — <?= e(format_date_id($report['end'])) ?></p>
            </div>
            <div class="print-meta">
                <strong>E-Posyandu MVP</strong>
                <span>Posyandu: <?= e((string) (auth_user()['id_posyandu'] ?? '-')) ?></span>
                <span>Dicetak: <?= e(date('d-m-Y H:i')) ?></span>
            </div>
        </header>

        <div class="print-summary">
            <div><span>Total pengukuran</span><strong><?= e((string) $report['summary']['total']) ?></strong></div>
            <div><span>Balita terukur</span><strong><?= e((string) $report['summary']['children']) ?></strong></div>
            <div><span>Indikasi demo</span><strong><?= e((string) $report['summary']['indication']) ?></strong></div>
            <div><span>Normal demo</span><strong><?= e((string) $report['summary']['normal']) ?></strong></div>
        </div>

        <p class="print-disclaimer">Hasil pada laporan ini merupakan simulasi akademik IF-ELSE dan bukan diagnosis klinis.</p>

        <?php if ($report['rows'] === []): ?>
            <p>Tidak ada data pengukuran pada periode ini.</p>
        <?php else: ?>
            <table class="print-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama balita</th>
                        <th>JK</th>
                        <th>Wali</th>
                        <th>Periksa</th>
                        <th>Usia</th>
                        <th>BB</th>
                        <th>TB</th>
                        <th>Hasil demo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($report['rows'] as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= e($row['kode_balita']) ?></td>
                        <td><?= e($row['nama_balita']) ?></td>
                        <td><?= e($row['jenis_kelamin']) ?></td>
                        <td><?= e($row['nama_wali']) ?></td>
                        <td><?= e(format_date_id((string) $row['tgl_periksa'])) ?></td>
                        <td><?= e((string) $row['umur_bulan']) ?></td>
                        <td><?= e((string) $row['berat_badan_kg']) ?></td>
                        <td><?= e((string) $row['tinggi_badan_cm']) ?></td>
                        <td><?= e((string) ($row['status_stunting'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/footer.php'; ?>