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
    redirect(app_url('modules/timbangan/index.php'));
}

$validation = validate_measurement_payload($_POST);
$data = $validation['data'];
$errors = $validation['errors'];
$child = $validation['child'];
$assessment = $validation['assessment'];

if ($errors !== [] || $child === null || $assessment === null) {
    measurement_form_redirect($data, $errors !== [] ? $errors : ['form' => 'Data pengukuran belum valid.']);
}

$standard = find_demo_standard();
if ($standard === null) {
    measurement_form_redirect($data, ['form' => 'Standar demo belum tersedia. Import ulang seed database.']);
}

try {
    $duplicate = db()->prepare(
        'SELECT id_timbangan
         FROM timbangan_bulanan
         WHERE id_balita = :id_balita
           AND tgl_periksa = :tgl_periksa
           AND deleted_at IS NULL
         LIMIT 1'
    );
    $duplicate->execute([
        'id_balita'   => (int) $data['id_balita'],
        'tgl_periksa' => $data['tgl_periksa'],
    ]);

    if ($duplicate->fetch()) {
        measurement_form_redirect($data, ['tgl_periksa' => 'Sudah ada pengukuran pada tanggal tersebut.']);
    }

    $pdo = db();
    $pdo->beginTransaction();

    $insertMeasurement = $pdo->prepare(
        'INSERT INTO timbangan_bulanan
            (id_balita, tgl_periksa, umur_bulan, berat_badan_kg, tinggi_badan_cm,
             jenis_pengukuran, sumber_data, catatan, created_by_user_id)
         VALUES
            (:id_balita, :tgl_periksa, :umur_bulan, :berat_badan_kg, :tinggi_badan_cm,
             :jenis_pengukuran, :sumber_data, :catatan, :created_by_user_id)'
    );
    $insertMeasurement->execute([
        'id_balita'        => (int) $data['id_balita'],
        'tgl_periksa'      => $data['tgl_periksa'],
        'umur_bulan'       => (float) $data['umur_bulan'],
        'berat_badan_kg'   => (float) $data['berat_badan'],
        'tinggi_badan_cm'  => (float) $data['tinggi_badan'],
        'jenis_pengukuran' => $data['jenis_pengukuran'],
        'sumber_data'      => 'web',
        'catatan'          => $data['catatan'] !== '' ? $data['catatan'] : null,
        'created_by_user_id' => current_user_id() ?: null,
    ]);
    $measurementId = (int) $pdo->lastInsertId();

    $insertAssessment = $pdo->prepare(
        'INSERT INTO hasil_antropometri
            (id_timbangan, id_standar, tb_u_zscore, bb_u_zscore, bb_tb_zscore,
             status_stunting, status_gizi, perlu_tinjauan, review_status, catatan)
         VALUES
            (:id_timbangan, :id_standar, NULL, NULL, NULL,
             :status_stunting, :status_gizi, :perlu_tinjauan, :review_status, :catatan)'
    );
    $insertAssessment->execute([
        'id_timbangan'   => $measurementId,
        'id_standar'     => (int) $standard['id_standar'],
        'status_stunting' => $assessment['status_stunting'],
        'status_gizi'     => $assessment['status_gizi'],
        'perlu_tinjauan'  => $assessment['perlu_tinjauan'],
        'review_status'   => $assessment['perlu_tinjauan'] === 1 ? 'needs_review' : 'pending',
        'catatan'         => $assessment['catatan'],
    ]);

    write_audit('create', 'timbangan', $measurementId, 'Pengukuran sintetis disimpan dan dinilai dengan assessor demo.');
    $pdo->commit();

    $message = $assessment['status_stunting'] === 'demo_indikasi_stunting'
        ? 'Timbangan tersimpan. Hasil: indikasi demo, perlu ditinjau.'
        : 'Timbangan tersimpan. Hasil: normal demo.';
    set_flash('success', $message);
    redirect(app_url('modules/timbangan/index.php'));
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Gagal menyimpan timbangan: ' . $exception->getMessage());
    measurement_form_redirect($data, ['form' => 'Data tidak dapat disimpan. Silakan coba lagi.']);
}