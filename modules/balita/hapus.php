<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();
auth_require_role('supervisor', 'admin');

if (!is_post()) {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}

$csrfValue = $_POST['csrf_token'] ?? null;
if (!is_string($csrfValue) || !csrf_verify($csrfValue)) {
    set_flash('error', 'Sesi formulir tidak valid. Silakan coba lagi.');
    redirect(app_url('modules/balita/index.php'));
}

$idValue = request_string($_POST, 'id_balita', 12);
$childId = ctype_digit($idValue) ? (int) $idValue : 0;
$child = find_child_for_user($childId);

if ($child === null) {
    set_flash('error', 'Data balita tidak ditemukan atau tidak dapat diakses.');
    redirect(app_url('modules/balita/index.php'));
}

try {
    $statement = db()->prepare(
        'UPDATE data_balita
         SET is_active = 0
         WHERE id_balita = :id_balita AND id_posyandu = :id_posyandu'
    );
    $statement->execute([
        'id_balita'   => $childId,
        'id_posyandu' => current_posyandu_id(),
    ]);

    write_audit('archive', 'balita', $childId, 'Data balita sintetis diarsipkan (soft delete).');
    set_flash('success', 'Data balita berhasil diarsipkan. Riwayat tidak dihapus permanen.');
} catch (PDOException $exception) {
    error_log('Gagal mengarsipkan balita: ' . $exception->getMessage());
    set_flash('error', 'Data balita tidak dapat diarsipkan.');
}

redirect(app_url('modules/balita/index.php'));