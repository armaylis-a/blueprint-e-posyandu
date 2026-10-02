<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$idValue = request_string($_GET, 'id', 12);
$childId = ctype_digit($idValue) ? (int) $idValue : 0;
$child = null;
$consent = null;

if ($childId > 0) {
    $child = find_child_for_user($childId);

    if ($child === null) {
        set_flash('error', 'Data balita tidak ditemukan atau tidak dapat diakses.');
        redirect(app_url('modules/balita/index.php'));
    }

    $consent = latest_consent_for_child($childId);
}

$formState = $_SESSION['child_form'] ?? null;
unset($_SESSION['child_form']);

$errors = is_array($formState) && is_array($formState['errors'] ?? null)
    ? $formState['errors']
    : [];
$old = is_array($formState) && is_array($formState['data'] ?? null)
    ? $formState['data']
    : [];

$data = [
    'kode_balita'   => (string) ($old['kode_balita'] ?? ($child['kode_balita'] ?? '')),
    'nama_balita'   => (string) ($old['nama_balita'] ?? ($child['nama_balita'] ?? '')),
    'jenis_kelamin' => (string) ($old['jenis_kelamin'] ?? ($child['jenis_kelamin'] ?? '')),
    'tgl_lahir'     => (string) ($old['tgl_lahir'] ?? ($child['tgl_lahir'] ?? '')),
    'nama_wali'     => (string) ($old['nama_wali'] ?? ($child['nama_wali'] ?? '')),
    'tgl_daftar'    => (string) ($old['tgl_daftar'] ?? ($child['tgl_daftar'] ?? today_iso())),
    'persetujuan'   => array_key_exists('persetujuan', $old)
        ? (bool) $old['persetujuan']
        : (is_array($consent) && ($consent['status_persetujuan'] ?? '') === 'granted'),
];

$pageTitle = $childId > 0 ? 'Edit Balita' : 'Tambah Balita';
$showNavigation = true;
$bodyClass = 'module-page';
require __DIR__ . '/../../partials/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">DATA BALITA · DATA SINTETIS</p>
        <h1><?= $childId > 0 ? 'Edit Data Balita' : 'Tambah Balita' ?></h1>
        <p class="muted">Gunakan data fiktif untuk kebutuhan demonstrasi akademik.</p>
    </div>
    <a class="button button-secondary" href="<?= e(app_url('modules/balita/index.php')) ?>">Kembali ke daftar</a>
</section>

<?php if ($errors !== []): ?>
    <div class="alert alert-error" role="alert">
        <?= e(field_error($errors, 'form') ?? 'Periksa kembali isian yang ditandai.') ?>
    </div>
<?php endif; ?>

<section class="panel form-panel">
    <form method="post" action="<?= e(app_url('modules/balita/simpan.php')) ?>" class="data-form">
        <?= csrf_field() ?>
        <input type="hidden" name="id_balita" value="<?= $childId > 0 ? $childId : '' ?>">

        <div class="form-grid">
            <div class="field">
                <label for="kode_balita">Kode balita <span class="required">*</span></label>
                <input id="kode_balita" name="kode_balita" type="text" value="<?= e($data['kode_balita']) ?>" maxlength="40" placeholder="BALITA-DEMO-009" required>
                <?php if ($message = field_error($errors, 'kode_balita')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
            </div>

            <div class="field">
                <label for="nama_balita">Nama balita <span class="required">*</span></label>
                <input id="nama_balita" name="nama_balita" type="text" value="<?= e($data['nama_balita']) ?>" maxlength="100" placeholder="Anak Demo 009" required>
                <?php if ($message = field_error($errors, 'nama_balita')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
            </div>

            <div class="field">
                <label for="jenis_kelamin">Jenis kelamin <span class="required">*</span></label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">Pilih</option>
                    <option value="L" <?= $data['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $data['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
                <?php if ($message = field_error($errors, 'jenis_kelamin')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
            </div>

            <div class="field">
                <label for="tgl_lahir">Tanggal lahir <span class="required">*</span></label>
                <input id="tgl_lahir" name="tgl_lahir" type="date" value="<?= e($data['tgl_lahir']) ?>" max="<?= e(today_iso()) ?>" required>
                <?php if ($message = field_error($errors, 'tgl_lahir')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
            </div>

            <div class="field">
                <label for="nama_wali">Nama wali <span class="required">*</span></label>
                <input id="nama_wali" name="nama_wali" type="text" value="<?= e($data['nama_wali']) ?>" maxlength="100" placeholder="Wali Demo 009" required>
                <?php if ($message = field_error($errors, 'nama_wali')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
            </div>

            <div class="field">
                <label for="tgl_daftar">Tanggal daftar <span class="required">*</span></label>
                <input id="tgl_daftar" name="tgl_daftar" type="date" value="<?= e($data['tgl_daftar']) ?>" max="<?= e(today_iso()) ?>" required>
                <?php if ($message = field_error($errors, 'tgl_daftar')): ?><small class="field-error"><?= e($message) ?></small><?php endif; ?>
            </div>
        </div>

        <div class="checkbox-field">
            <input id="persetujuan" name="persetujuan" type="checkbox" value="1" <?= $data['persetujuan'] ? 'checked' : '' ?>>
            <label for="persetujuan">Persetujuan wali/pihak berwenang tercatat untuk data demo ini.</label>
        </div>

        <div class="form-actions">
            <a class="button button-ghost" href="<?= e(app_url('modules/balita/index.php')) ?>">Batal</a>
            <button class="button button-primary" type="submit"><?= $childId > 0 ? 'Simpan Perubahan' : 'Simpan Balita' ?></button>
        </div>
    </form>
</section>

<section class="notice-panel">
    <strong>Data minimization.</strong>
    Formulir ini sengaja tidak meminta NIK, nomor telepon, atau alamat. Jangan menambahkan data pribadi nyata pada MVP/demo.
</section>

<?php require __DIR__ . '/../../partials/footer.php'; ?>