<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$children = [];
$databaseReady = true;

try {
    $children = active_children_for_user();
} catch (PDOException $exception) {
    error_log('Gagal memuat pilihan balita: ' . $exception->getMessage());
    $databaseReady = false;
}

$formState = $_SESSION['measurement_form'] ?? null;
unset($_SESSION['measurement_form']);

$errors = is_array($formState) && is_array($formState['errors'] ?? null)
    ? $formState['errors']
    : [];
$old = is_array($formState) && is_array($formState['data'] ?? null)
    ? $formState['data']
    : [];

$selectedChild = (string) ($old['id_balita'] ?? request_string($_GET, 'id_balita', 12));
$selectedDate = (string) ($old['tgl_periksa'] ?? today_iso());
$selectedWeight = (string) ($old['berat_badan'] ?? '');
$selectedHeight = (string) ($old['tinggi_badan'] ?? '');
$note = (string) ($old['catatan'] ?? '');

$pageTitle = 'Input Timbangan';
$showNavigation = true;
$bodyClass = 'module-page';
require __DIR__ . '/../../partials/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">MODUL TIMBANGAN · DATA SINTETIS</p>
        <h1>Input Timbangan Bulanan</h1>
        <p class="muted">Usia dan metode ukur dihitung server dari tanggal lahir balita.</p>
    </div>
    <a class="button button-secondary" href="<?= e(app_url('modules/timbangan/index.php')) ?>">Kembali ke riwayat</a>
</section>

<div class="alert alert-warning" role="note">
    <strong>Assessor demo.</strong>
    Sistem menggunakan ambang tinggi IF-ELSE dari blueprint. Ini bukan z-score WHO/Kemenkes dan bukan diagnosis.
</div>

<?php if (!$databaseReady): ?>
    <div class="alert alert-error" role="alert">Database tidak dapat diakses. Periksa konfigurasi dan import schema/seed.</div>
<?php endif; ?>

<?php if ($errors !== []): ?>
    <div class="alert alert-error" role="alert">
        <?= e(field_error($errors, 'form') ?? 'Periksa kembali isian yang ditandai.') ?>
    </div>
<?php endif; ?>

<?php if ($children === []): ?>
    <section class="panel">
        <div class="empty-state-box">
            <strong>Belum ada balita aktif.</strong>
            <p>Tambahkan data balita sintetis sebelum melakukan input timbangan.</p>
            <a class="button button-primary button-small" href="<?= e(app_url('modules/balita/form.php')) ?>">Tambah balita</a>
        </div>
    </section>
<?php else: ?>
    <section class="panel form-panel">
        <form method="post" action="<?= e(app_url('modules/timbangan/simpan.php')) ?>" class="data-form">
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="field">
                    <label for="id_balita">Pilih balita <span class="required">*</span></label>
                    <select id="id_balita" name="id_balita" required>
                        <option value="">Pilih data balita</option>
                        <?php foreach ($children as $child): ?>
                            <option value="<?= (int) $child['id_balita'] ?>" <?= $selectedChild === (string) $child['id_balita'] ? 'selected' : '' ?>>
                                <?= e($child['kode_balita']) ?> — <?= e($child['nama_balita']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($message = field_error($errors, 'id_balita')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="tgl_periksa">Tanggal pemeriksaan <span class="required">*</span></label>
                    <input id="tgl_periksa" name="tgl_periksa" type="date" value="<?= e($selectedDate) ?>" max="<?= e(today_iso()) ?>" required>
                    <?php if ($message = field_error($errors, 'tgl_periksa')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="berat_badan">Berat badan (kg) <span class="required">*</span></label>
                    <input id="berat_badan" name="berat_badan" type="number" value="<?= e($selectedWeight) ?>" min="0.1" max="200" step="0.01" placeholder="Contoh: 9.50" required>
                    <?php if ($message = field_error($errors, 'berat_badan')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="tinggi_badan">Tinggi badan (cm) <span class="required">*</span></label>
                    <input id="tinggi_badan" name="tinggi_badan" type="number" value="<?= e($selectedHeight) ?>" min="1" max="200" step="0.01" placeholder="Contoh: 68.50" required>
                    <?php if ($message = field_error($errors, 'tinggi_badan')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="catatan">Catatan</label>
                <textarea id="catatan" name="catatan" rows="3" maxlength="255" placeholder="Catatan sintetis, misalnya alat ukur demo."><?= e($note) ?></textarea>
            </div>

            <div class="checkbox-field">
                <span class="info-icon" aria-hidden="true">i</span>
                <span>Metode ukur dipilih otomatis: panjang berbaring untuk usia &lt; 24 bulan dan tinggi berdiri untuk usia ≥ 24 bulan.</span>
            </div>

            <div class="form-actions">
                <a class="button button-ghost" href="<?= e(app_url('modules/timbangan/index.php')) ?>">Batal</a>
                <button class="button button-primary" type="submit">Simpan &amp; Hitung Demo</button>
            </div>
        </form>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/footer.php'; ?>