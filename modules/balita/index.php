<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$search = request_string($_GET, 'q', 100);
$status = request_string($_GET, 'status', 20);
$status = in_array($status, ['active', 'inactive', 'all'], true) ? $status : 'active';
$children = [];
$databaseReady = true;

try {
    $where = ['b.id_posyandu = :id_posyandu'];

    if ($status !== 'all') {
        $where[] = 'b.is_active = :is_active';
    }

    if ($search !== '') {
        $where[] = '(b.kode_balita LIKE :search OR b.nama_balita LIKE :search OR b.nama_wali LIKE :search)';
    }

    $sql = 'SELECT b.id_balita, b.kode_balita, b.nama_balita, b.jenis_kelamin,
                   b.tgl_lahir, b.nama_wali, b.tgl_daftar, b.is_active,
                   (SELECT c.status_persetujuan
                      FROM consent_records c
                     WHERE c.id_balita = b.id_balita
                     ORDER BY c.id_consent DESC LIMIT 1) AS status_persetujuan
            FROM data_balita b
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY b.nama_balita ASC, b.kode_balita ASC
            LIMIT 100';

    $statement = db()->prepare($sql);
    $parameters = ['id_posyandu' => current_posyandu_id()];

    if ($status !== 'all') {
        $parameters['is_active'] = $status === 'active' ? 1 : 0;
    }

    if ($search !== '') {
        $parameters['search'] = '%' . $search . '%';
    }

    $statement->execute($parameters);
    $children = $statement->fetchAll();
} catch (PDOException $exception) {
    error_log('Gagal memuat daftar balita: ' . $exception->getMessage());
    $databaseReady = false;
}

$pageTitle = 'Data Balita';
$showNavigation = true;
$bodyClass = 'module-page';
require __DIR__ . '/../../partials/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">MODUL DATA · DATA SINTETIS</p>
        <h1>Data Balita</h1>
        <p class="muted">Kelola data contoh balita untuk modul MVP. Tidak menggunakan data pribadi nyata.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('modules/balita/form.php')) ?>">+ Tambah Balita</a>
</section>

<?php if (!$databaseReady): ?>
    <div class="alert alert-error" role="alert">Database tidak dapat diakses. Periksa konfigurasi dan import schema/seed.</div>
<?php endif; ?>

<section class="panel filter-panel">
    <form method="get" action="<?= e(app_url('modules/balita/index.php')) ?>" class="filter-form">
        <div class="field field-grow">
            <label for="q">Cari balita</label>
            <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Kode, nama balita, atau wali">
        </div>
        <div class="field">
            <label for="status">Status data</label>
            <select id="status" name="status">
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Diarsipkan</option>
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Semua</option>
            </select>
        </div>
        <button class="button button-secondary" type="submit">Terapkan</button>
        <?php if ($search !== '' || $status !== 'active'): ?>
            <a class="button button-ghost" href="<?= e(app_url('modules/balita/index.php')) ?>">Reset</a>
        <?php endif; ?>
    </form>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">DAFTAR DATA</p>
            <h2><?= count($children) ?> data ditemukan</h2>
        </div>
    </div>

    <?php if ($children === []): ?>
        <div class="empty-state-box">
            <strong>Belum ada data yang cocok.</strong>
            <p>Tambahkan balita sintetis atau ubah kata kunci pencarian.</p>
            <a class="button button-primary button-small" href="<?= e(app_url('modules/balita/form.php')) ?>">Tambah balita</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama balita</th>
                        <th>Jenis kelamin</th>
                        <th>Tanggal lahir</th>
                        <th>Wali</th>
                        <th>Persetujuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($children as $child): ?>
                    <?php
                    $isActive = (int) ($child['is_active'] ?? 0) === 1;
                    $consent = (string) ($child['status_persetujuan'] ?? 'not_recorded');
                    ?>
                    <tr>
                        <td><code><?= e($child['kode_balita']) ?></code></td>
                        <td>
                            <strong><?= e($child['nama_balita']) ?></strong>
                            <?php if (!$isActive): ?><br><span class="status status-planned">Diarsipkan</span><?php endif; ?>
                        </td>
                        <td><?= e($child['jenis_kelamin']) ?></td>
                        <td><?= e(format_date_id((string) $child['tgl_lahir'])) ?></td>
                        <td><?= e($child['nama_wali']) ?></td>
                        <td>
                            <?php if ($consent === 'granted'): ?>
                                <span class="status status-done">Granted</span>
                            <?php elseif ($consent === 'withdrawn'): ?>
                                <span class="status status-review">Withdrawn</span>
                            <?php else: ?>
                                <span class="status status-planned">Belum tercatat</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="text-action" href="<?= e(app_url('modules/balita/detail.php?id=' . (int) $child['id_balita'])) ?>">Detail</a>
                                <a class="text-action" href="<?= e(app_url('modules/balita/form.php?id=' . (int) $child['id_balita'])) ?>">Edit</a>
                                <?php if ($isActive && in_array((string) (auth_user()['role'] ?? ''), ['supervisor', 'admin'], true)): ?>
                                    <form method="post" action="<?= e(app_url('modules/balita/hapus.php')) ?>" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_balita" value="<?= (int) $child['id_balita'] ?>">
                                        <button class="text-action text-danger" type="submit">Arsipkan</button>
                                    </form>
                                <?php endif; ?>
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