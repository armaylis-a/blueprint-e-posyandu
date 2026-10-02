<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();
auth_require_role('kader', 'supervisor', 'admin');

if (!is_post()) {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}

$csrfValue = $_POST['csrf_token'] ?? null;
if (!is_string($csrfValue) || !csrf_verify($csrfValue)) {
    set_flash('error', 'Sesi formulir tidak valid. Silakan buka ulang formulir.');
    redirect(app_url('modules/balita/index.php'));
}

$idValue = request_string($_POST, 'id_balita', 12);
$childId = ctype_digit($idValue) ? (int) $idValue : 0;
$validation = validate_child_payload($_POST);
$data = $validation['data'];
$errors = $validation['errors'];

if ($errors !== []) {
    child_form_redirect($data, $errors, $childId);
}

try {
    $duplicateStatement = db()->prepare(
        'SELECT id_balita
         FROM data_balita
         WHERE kode_balita = :kode_balita AND id_balita <> :id_balita
         LIMIT 1'
    );
    $duplicateStatement->execute([
        'kode_balita' => $data['kode_balita'],
        'id_balita'   => $childId,
    ]);

    if ($duplicateStatement->fetch()) {
        child_form_redirect($data, ['kode_balita' => 'Kode balita sudah digunakan.'], $childId);
    }

    $pdo = db();
    $pdo->beginTransaction();

    if ($childId > 0) {
        $existing = find_child_for_user($childId);

        if ($existing === null) {
            $pdo->rollBack();
            set_flash('error', 'Data balita tidak ditemukan atau tidak dapat diakses.');
            redirect(app_url('modules/balita/index.php'));
        }

        $update = $pdo->prepare(
            'UPDATE data_balita
             SET kode_balita = :kode_balita,
                 nama_balita = :nama_balita,
                 jenis_kelamin = :jenis_kelamin,
                 tgl_lahir = :tgl_lahir,
                 nama_wali = :nama_wali,
                 tgl_daftar = :tgl_daftar,
                 is_active = 1
             WHERE id_balita = :id_balita AND id_posyandu = :id_posyandu'
        );
        $update->execute([
            'kode_balita'   => $data['kode_balita'],
            'nama_balita'   => $data['nama_balita'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tgl_lahir'     => $data['tgl_lahir'],
            'nama_wali'     => $data['nama_wali'],
            'tgl_daftar'    => $data['tgl_daftar'],
            'id_balita'     => $childId,
            'id_posyandu'   => current_posyandu_id(),
        ]);

        if ($data['persetujuan']) {
            $latestConsent = latest_consent_for_child($childId);
            if (!is_array($latestConsent) || ($latestConsent['status_persetujuan'] ?? '') !== 'granted') {
                $consent = $pdo->prepare(
                    'INSERT INTO consent_records
                        (id_balita, status_persetujuan, tanggal_persetujuan, catatan)
                     VALUES
                        (:id_balita, :status, :tanggal, :catatan)'
                );
                $consent->execute([
                    'id_balita' => $childId,
                    'status'    => 'granted',
                    'tanggal'   => today_iso(),
                    'catatan'   => 'Persetujuan dicatat dari formulir demo.',
                ]);
            }
        }

        write_audit('update', 'balita', $childId, 'Data balita sintetis diperbarui.');
        $pdo->commit();

        set_flash('success', 'Data balita berhasil diperbarui.');
        redirect(app_url('modules/balita/detail.php?id=' . $childId));
    }

    $insert = $pdo->prepare(
        'INSERT INTO data_balita
            (id_posyandu, kode_balita, nama_balita, jenis_kelamin, tgl_lahir,
             nama_wali, tgl_daftar, is_active, created_by_user_id)
         VALUES
            (:id_posyandu, :kode_balita, :nama_balita, :jenis_kelamin, :tgl_lahir,
             :nama_wali, :tgl_daftar, 1, :created_by_user_id)'
    );
    $insert->execute([
        'id_posyandu'      => current_posyandu_id(),
        'kode_balita'      => $data['kode_balita'],
        'nama_balita'      => $data['nama_balita'],
        'jenis_kelamin'    => $data['jenis_kelamin'],
        'tgl_lahir'        => $data['tgl_lahir'],
        'nama_wali'        => $data['nama_wali'],
        'tgl_daftar'       => $data['tgl_daftar'],
        'created_by_user_id' => current_user_id() ?: null,
    ]);
    $newChildId = (int) $pdo->lastInsertId();

    if ($data['persetujuan']) {
        $consent = $pdo->prepare(
            'INSERT INTO consent_records
                (id_balita, status_persetujuan, tanggal_persetujuan, catatan)
             VALUES
                (:id_balita, :status, :tanggal, :catatan)'
        );
        $consent->execute([
            'id_balita' => $newChildId,
            'status'    => 'granted',
            'tanggal'   => today_iso(),
            'catatan'   => 'Persetujuan sintetis dicatat dari formulir demo.',
        ]);
    }

    write_audit('create', 'balita', $newChildId, 'Data balita sintetis dibuat.');
    $pdo->commit();

    set_flash('success', 'Data balita berhasil ditambahkan.');
    redirect(app_url('modules/balita/detail.php?id=' . $newChildId));
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Gagal menyimpan data balita: ' . $exception->getMessage());
    child_form_redirect($data, ['form' => 'Data tidak dapat disimpan. Periksa kembali formulir.'], $childId);
}