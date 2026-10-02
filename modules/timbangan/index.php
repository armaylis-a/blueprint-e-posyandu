<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$rows = [];
$databaseReady = true;

try {
    $statement = db()->prepare(
        'SELECT t.id_timbangan, t.tgl_periksa, t.umur_bulan,
                t.berat_badan_kg, t.tinggi_badan_cm, t.jenis_pengukuran,
                b.id_balita, b.kode_balita, b.nama_balita,
                h.status_stunting, h.status_gizi, h.perlu_tinjauan, h.review_status
         FROM timbangan_bulanan t
         INNER JOIN data_balita b ON b.id_balita = t.id_balita
         LEFT JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan
         WHERE b.id_posyandu = :id_posyandu AND t.deleted_at IS NULL
         ORDER BY t.tgl_periksa DESC, t.id_timbangan DESC
         LIMIT 100'
    );
    $statement->execute(['id_posyandu' => current_posyandu_id()]);
    $rows = $statement->fetchAll();
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
        <p class="eyebrow">MODUL TIMBANGAN · DATA SINTETIS</p>
        <h1>Timbangan Balita</h1>
        <p class="muted">Input berat dan tinggi, lalu assessor demo berjalan otomatis.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('modules/timbangan/form.php')) ?>">+ Input Timbangan</a>
</section>

<div class="alert alert-warning" role="note">
    <strong>Simulasi akademik.</strong>
    Threshold yang digunakan masih IF-ELSE dari blueprint, bukan standar z-score WHO/Kemenkes. Hasil tidak boleh digunakan sebagai diagnosis.
</div>

<?php if (!$databaseReady): ?>
    <div class="alert alert-error" role="alert">Database tidak dapat diakses. Periksa konfigurasi dan import schema/seed.</div>
<?php endif; ?>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">RIWAYAT TIMBANGAN</p>
            <h2><?= count($rows) ?> data ditemukan</h2>
        </div>
    </div>

    <?php if ($rows === []): ?>
        <div class="empty-state-box">
            <strong>Belum ada data timbangan.</strong>
            <p>Mulai dengan menginput pengukuran balita sintetis.</p>
            <a class="button button-primary button-small" href="<?= e(app_url('modules/timbangan/form.php')) ?>">Input timbangan</a>
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
                        <th>Hasil demo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <strong><?= e($row['kode_balita']) ?></strong><br>
                            <small><?= e($row['nama_balita']) ?></small>
                        </td>
                        <td><?= e(format_date_id((string) $row['tgl_periksa'])) ?></td>
                        <td><?= e((string) $row['umur_bulan']) ?> bulan</td>
                        <td><?= e((string) $row['berat_badan_kg']) ?> kg</td>
                        <td><?= e((string) $row['tinggi_badan_cm']) ?> cm</td>
                        <td><?= e($row['jenis_pengukuran'] === 'panjang_baring' ? 'Panjang berbaring' : 'Tinggi berdiri') ?></td>
                        <td>
                            <?php if ((string) ($row['status_stunting'] ?? '') === 'demo_indikasi_stunting'): ?>
                                <span class="status status-review">Indikasi demo</span>
                            <?php elseif ((string) ($row['status_stunting'] ?? '') !== ''): ?>
                                <span class="status status-done">Normal demo</span>
                            <?php else: ?>
                                <span class="status status-planned">Belum dihitung</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="text-action" href="<?= e(app_url('modules/balita/detail.php?id=' . (int) $row['id_balita'])) ?>">Detail balita</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../../partials/footer.php'; ?>