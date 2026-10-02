<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$idValue = request_string($_GET, 'id', 12);
$childId = ctype_digit($idValue) ? (int) $idValue : 0;
$child = find_child_for_user($childId);

if ($child === null) {
    set_flash('error', 'Data balita tidak ditemukan atau tidak dapat diakses.');
    redirect(app_url('modules/balita/index.php'));
}

$consent = latest_consent_for_child($childId);
$measurements = [];

try {
    $statement = db()->prepare(
        'SELECT t.id_timbangan, t.tgl_periksa, t.umur_bulan,
                t.berat_badan_kg, t.tinggi_badan_cm, t.jenis_pengukuran,
                h.status_stunting, h.status_gizi, h.review_status, h.perlu_tinjauan
         FROM timbangan_bulanan t
         LEFT JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan
         WHERE t.id_balita = :id_balita AND t.deleted_at IS NULL
         ORDER BY t.tgl_periksa DESC, t.id_timbangan DESC
         LIMIT 24'
    );
    $statement->execute(['id_balita' => $childId]);
    $measurements = $statement->fetchAll();
} catch (PDOException $exception) {
    error_log('Gagal memuat detail balita: ' . $exception->getMessage());
}

$pageTitle = 'Detail Balita';
$showNavigation = true;
$bodyClass = 'module-page';
require __DIR__ . '/../../partials/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">DETAIL DATA · DATA SINTETIS</p>
        <h1><?= e($child['nama_balita']) ?></h1>
        <p class="muted"><code><?= e($child['kode_balita']) ?></code> · <?= (int) $child['is_active'] === 1 ? 'Data aktif' : 'Diarsipkan' ?></p>
    </div>
    <div class="heading-actions">
        <a class="button button-secondary" href="<?= e(app_url('modules/balita/form.php?id=' . $childId)) ?>">Edit Data</a>
        <a class="button button-ghost" href="<?= e(app_url('modules/balita/index.php')) ?>">Kembali</a>
    </div>
</section>

<section class="detail-grid">
    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">BIODATA</p>
                <h2>Informasi balita</h2>
            </div>
        </div>
        <dl class="detail-list">
            <div><dt>Nama balita</dt><dd><?= e($child['nama_balita']) ?></dd></div>
            <div><dt>Jenis kelamin</dt><dd><?= e($child['jenis_kelamin']) ?></dd></div>
            <div><dt>Tanggal lahir</dt><dd><?= e(format_date_id((string) $child['tgl_lahir'])) ?></dd></div>
            <div><dt>Nama wali</dt><dd><?= e($child['nama_wali']) ?></dd></div>
            <div><dt>Tanggal daftar</dt><dd><?= e(format_date_id((string) $child['tgl_daftar'])) ?></dd></div>
            <div><dt>Dibuat</dt><dd><?= e(format_date_id((string) $child['created_at'])) ?></dd></div>
        </dl>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">PRIVASI</p>
                <h2>Persetujuan</h2>
            </div>
        </div>
        <?php $consentStatus = is_array($consent) ? (string) $consent['status_persetujuan'] : 'not_recorded'; ?>
        <?php if ($consentStatus === 'granted'): ?>
            <div class="consent-box consent-granted">
                <strong>Tercatat</strong>
                <span><?= e(format_date_id((string) ($consent['tanggal_persetujuan'] ?? null))) ?></span>
            </div>
        <?php elseif ($consentStatus === 'withdrawn'): ?>
            <div class="consent-box consent-withdrawn">
                <strong>Dicabut</strong>
                <span><?= e(format_date_id((string) ($consent['tanggal_persetujuan'] ?? null))) ?></span>
            </div>
        <?php else: ?>
            <div class="consent-box consent-missing">
                <strong>Belum tercatat</strong>
                <span>Status persetujuan perlu dilengkapi.</span>
            </div>
        <?php endif; ?>
        <p class="muted detail-note">Data demo tidak memuat NIK, telepon, atau alamat.</p>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">RIWAYAT</p>
            <h2>Pengukuran berat dan tinggi</h2>
        </div>
        <span class="status status-planned">Modul timbangan segera hadir</span>
    </div>

    <?php if ($measurements === []): ?>
        <div class="empty-state-box">
            <strong>Belum ada pengukuran.</strong>
            <p>Riwayat timbangan akan tampil setelah modul pengukuran tersedia.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Usia</th>
                        <th>Berat</th>
                        <th>Tinggi</th>
                        <th>Metode</th>
                        <th>Status demo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($measurements as $measurement): ?>
                    <tr>
                        <td><?= e(format_date_id((string) $measurement['tgl_periksa'])) ?></td>
                        <td><?= e((string) $measurement['umur_bulan']) ?> bulan</td>
                        <td><?= e((string) $measurement['berat_badan_kg']) ?> kg</td>
                        <td><?= e((string) $measurement['tinggi_badan_cm']) ?> cm</td>
                        <td><?= e($measurement['jenis_pengukuran'] === 'panjang_baring' ? 'Panjang berbaring' : 'Tinggi berdiri') ?></td>
                        <td>
                            <?php if ((string) ($measurement['status_stunting'] ?? '') === 'demo_indikasi_stunting'): ?>
                                <span class="status status-review">Indikasi demo</span>
                            <?php elseif ((string) ($measurement['status_stunting'] ?? '') !== ''): ?>
                                <span class="status status-done">Normal demo</span>
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

<section class="notice-panel">
    <strong>Catatan.</strong>
    Riwayat pengukuran bersifat demonstrasi. Status stunting pada modul ini belum menggunakan z-score WHO/Kemenkes.
</section>

<?php require __DIR__ . '/../../partials/footer.php'; ?>