<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$month = request_string($_GET, 'bulan', 7);
$report = null;
$databaseReady = true;

try {
    $report = fetch_monthly_report($month);
} catch (PDOException $exception) {
    error_log('Gagal memuat laporan bulanan: ' . $exception->getMessage());
    $databaseReady = false;
}

$pageTitle = 'Laporan Bulanan';
$showNavigation = true;
$bodyClass = 'module-page';
require __DIR__ . '/../../partials/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">MODUL LAPORAN · e-POSYANDU</p>
        <h1>Laporan Bulanan</h1>
        <p class="muted">Rekapitulasi pengukuran dan status gizi balita per periode bulanan.</p>
    </div>
    <?php if ($report !== null): ?>
        <div class="heading-actions">
            <a class="button button-secondary" href="<?= e(app_url('modules/laporan/ekspor.php?bulan=' . urlencode($report['month']))) ?>">Export CSV</a>
            <a class="button button-primary" href="<?= e(app_url('modules/laporan/cetak.php?bulan=' . urlencode($report['month']))) ?>">Cetak Laporan</a>
        </div>
    <?php endif; ?>
</section>

<?php if (!$databaseReady || $report === null): ?>
    <div class="alert alert-error" role="alert">Laporan tidak dapat dimuat. Periksa konfigurasi koneksi database.</div>
<?php else: ?>
    <section class="panel filter-panel">
        <form method="get" action="<?= e(app_url('modules/laporan/index.php')) ?>" class="filter-form">
            <div class="field">
                <label for="bulan">Periode bulan</label>
                <input id="bulan" name="bulan" type="month" value="<?= e($report['month']) ?>">
            </div>
            <button class="button button-secondary" type="submit">Tampilkan</button>
            <a class="button button-ghost" href="<?= e(app_url('modules/laporan/index.php')) ?>">Bulan ini</a>
        </form>
    </section>

    <section class="stats-grid report-stats" aria-label="Ringkasan laporan">
        <article class="stat-card">
            <span class="stat-label">Total pengukuran</span>
            <strong><?= e((string) $report['summary']['total']) ?></strong>
            <small><?= e($report['label']) ?></small>
        </article>
        <article class="stat-card">
            <span class="stat-label">Balita terukur</span>
            <strong><?= e((string) $report['summary']['children']) ?></strong>
            <small>Unik dalam periode</small>
        </article>
        <article class="stat-card">
            <span class="stat-label">Indikasi Stunting</span>
            <strong><?= e((string) $report['summary']['indication']) ?></strong>
            <small>Perlu ditinjau</small>
        </article>
        <article class="stat-card">
            <span class="stat-label">Normal</span>
            <strong><?= e((string) $report['summary']['normal']) ?></strong>
            <small>Hasil rekap</small>
        </article>
    </section>

    <div class="alert alert-warning" role="note">
        <strong>Catatan Sistem:</strong>
        Rekapitulasi laporan menggunakan standar penilaian sementara dan akan disesuaikan dengan standar Z-Score WHO/Kemenkes pada pengembangan tahap selanjutnya.
    </div>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">REKAP DATA</p>
                <h2><?= e($report['label']) ?></h2>
            </div>
            <span class="muted"><?= e(format_date_id($report['start'])) ?> — <?= e(format_date_id($report['end'])) ?></span>
        </div>

        <?php if ($report['rows'] === []): ?>
            <div class="empty-state-box">
                <strong>Belum ada pengukuran pada periode ini.</strong>
                <p>Pilih bulan lain atau tambahkan data timbangan balita.</p>
                <a class="button button-primary button-small" href="<?= e(app_url('modules/timbangan/form.php')) ?>">Input timbangan</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama balita</th>
                            <th>JK</th>
                            <th>Wali</th>
                            <th>Periksa</th>
                            <th>Usia</th>
                            <th>BB</th>
                            <th>TB</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($report['rows'] as $row): ?>
                        <?php
                        // Bersihkan -DEMO dari kode balita & nama balita
                        $cleanCode = str_replace('-DEMO', '', (string)($row['kode_balita'] ?? ''));
                        $cleanName = str_replace('Demo ', '', (string)($row['nama_balita'] ?? ''));

                        // Format Jenis Kelamin
                        $jkRaw = strtoupper((string)($row['jenis_kelamin'] ?? ''));
                        $jkLabel = $jkRaw === 'L' ? 'Laki-laki' : ($jkRaw === 'P' ? 'Perempuan' : $jkRaw);

                        // Format Tanggal Periksa ke Bahasa Indonesia
                        $rawDate = (string) ($row['tgl_periksa'] ?? '');
                        if ($rawDate !== '') {
                            $parts = explode('-', $rawDate);
                            if (count($parts) === 3) {
                                if (strlen($parts[0]) === 4) {
                                    $day = (int)$parts[2];
                                    $monthNum = (int)$parts[1];
                                    $year = $parts[0];
                                } else {
                                    $day = (int)$parts[0];
                                    $monthNum = (int)$parts[1];
                                    $year = $parts[2];
                                }
                                $bulanIndo = [
                                    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                ];
                                $namaBulan = $bulanIndo[$monthNum] ?? '';
                                $formattedDate = "{$day} {$namaBulan} {$year}";
                            } else {
                                $formattedDate = $rawDate;
                            }
                        } else {
                            $formattedDate = '-';
                        }
                        ?>
                        <tr>
                            <td><code><?= e($cleanCode) ?></code></td>
                            <td><?= e($cleanName) ?></td>
                            <td><?= e($jkLabel) ?></td>
                            <td><?= e($row['nama_wali']) ?></td>
                            <td><?= e($formattedDate) ?></td>
                            <td><?= e((string) $row['umur_bulan']) ?> bln</td>
                            <td><?= e((string) $row['berat_badan_kg']) ?> kg</td>
                            <td><?= e((string) $row['tinggi_badan_cm']) ?> cm</td>
                            <td>
                                <?php if ((string) ($row['status_stunting'] ?? '') === 'demo_indikasi_stunting'): ?>
                                    <span class="status status-review">Indikasi Stunting</span>
                                <?php elseif ((string) ($row['status_stunting'] ?? '') !== ''): ?>
                                    <span class="status status-done">Normal</span>
                                <?php else: ?>
                                    <span class="status status-planned">Belum dihitung</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/footer.php'; ?>