<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$measurements = [];
$databaseReady = true;

try {
    $statement = db()->prepare(
        'SELECT t.id_timbangan, t.tgl_periksa, t.umur_bulan, t.berat_badan_kg,
                t.tinggi_badan_cm, t.jenis_pengukuran, b.id_balita, b.kode_balita,
                b.nama_balita, h.status_stunting, h.review_status
         FROM timbangan_bulanan t
         INNER JOIN data_balita b ON b.id_balita = t.id_balita
         LEFT JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan
         WHERE b.id_posyandu = :id_posyandu AND t.deleted_at IS NULL
         ORDER BY t.tgl_periksa DESC, b.nama_balita ASC
         LIMIT 100'
    );
    $statement->execute(['id_posyandu' => current_posyandu_id()]);
    $measurements = $statement->fetchAll();
} catch (PDOException $exception) {
    error_log('Gagal memuat daftar timbangan: ' . $exception->getMessage());
    $databaseReady = false;
}

$pageTitle = 'Timbangan Balita';
$showNavigation = true;
$bodyClass = 'module-page';
require __DIR__ . '/../../partials/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">MODUL TIMBANGAN · e-POSYANDU</p>
        <h1>Timbangan Balita</h1>
        <p class="muted">Riwayat dan pencatatan hasil penimbangan balita.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('modules/timbangan/form.php')) ?>">+ Input Timbangan</a>
</section>

<?php if (!$databaseReady): ?>
    <div class="alert alert-error" role="alert">Database tidak dapat diakses. Periksa konfigurasi koneksi database.</div>
<?php endif; ?>

<section class="notice-panel" style="margin-bottom: 1.5rem;">
    <strong>Catatan Sistem:</strong> Penilaian kondisi gizi pada versi MVP ini menggunakan logika penilaian sementara dan akan disempurnakan dengan standar Z-Score WHO/Kemenkes pada tahap pengembangan selanjutnya.
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">RIWAYAT TIMBANGAN</p>
            <h2><?= count($measurements) ?> data ditemukan</h2>
        </div>
    </div>

    <?php if ($measurements === []): ?>
        <div class="empty-state-box">
            <strong>Belum ada riwayat timbangan.</strong>
            <p>Mulai catat data timbangan balita pertama kamu.</p>
            <a class="button button-primary button-small" href="<?= e(app_url('modules/timbangan/form.php')) ?>">+ Input Timbangan</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Balita</th>
                        <th>Tanggal</th>
                        <th>Usia</th>
                        <th>Berat</th>
                        <th>Tinggi</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($measurements as $item): ?>
                    <?php
                    // Bersihkan -DEMO dari kode balita & nama balita
                    $cleanCode = str_replace('-DEMO', '', (string)($item['kode_balita'] ?? ''));
                    $cleanName = str_replace('Demo ', '', (string)($item['nama_balita'] ?? ''));

                    // Pemetaan status gizi
                    $rawStatus = (string)($item['status_stunting'] ?? '');
                    if ($rawStatus === 'demo_indikasi_stunting' || $rawStatus === 'Indikasi demo') {
                        $statusLabel = 'Indikasi Stunting';
                        $statusClass = 'status-review';
                    } elseif ($rawStatus === 'demo_tidak_ada_indikasi' || $rawStatus === 'Normal demo') {
                        $statusLabel = 'Normal';
                        $statusClass = 'status-done';
                    } else {
                        $statusLabel = $rawStatus ?: '-';
                        $statusClass = 'status-done';
                    }

                    // Format Tanggal ke Bahasa Indonesia
                    $rawDate = (string) ($item['tgl_periksa'] ?? '');
                    if ($rawDate !== '') {
                        $parts = explode('-', $rawDate);
                        if (count($parts) === 3) {
                            if (strlen($parts[0]) === 4) {
                                $day = (int)$parts[2];
                                $month = (int)$parts[1];
                                $year = $parts[0];
                            } else {
                                $day = (int)$parts[0];
                                $month = (int)$parts[1];
                                $year = $parts[2];
                            }
                            $bulanIndo = [
                                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                            ];
                            $namaBulan = $bulanIndo[$month] ?? '';
                            $formattedDate = "{$day} {$namaBulan} {$year}";
                        } else {
                            $formattedDate = $rawDate;
                        }
                    } else {
                        $formattedDate = '-';
                    }

                    // Format metode pengukuran
                    $metodeRaw = (string)($item['jenis_pengukuran'] ?? '');
                    $metodeLabel = strtolower($metodeRaw) === 'telentang' || strtolower($metodeRaw) === 'panjang berbaring'
                        ? 'Panjang berbaring'
                        : 'Tinggi berdiri';
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($cleanCode) ?></strong><br>
                            <small><?= e($cleanName) ?></small>
                        </td>
                        <td><?= e($formattedDate) ?></td>
                        <td><?= number_format((float)($item['umur_bulan'] ?? 0), 2) ?> bulan</td>
                        <td><?= number_format((float)($item['berat_badan_kg'] ?? 0), 2) ?> kg</td>
                        <td><?= number_format((float)($item['tinggi_badan_cm'] ?? 0), 2) ?> cm</td>
                        <td><?= e($metodeLabel) ?></td>
                        <td>
                            <span class="status <?= $statusClass ?>">
                                <?= e($statusLabel) ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="text-action" href="<?= e(app_url('modules/balita/detail.php?id=' . (int) $item['id_balita'])) ?>">Detail balita</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../../partials/footer.php'; ?>