<?php

declare(strict_types=1);

function current_user_id(): int
{
    $user = auth_user();

    return is_array($user) ? (int) ($user['id_user'] ?? 0) : 0;
}

function current_posyandu_id(): int
{
    $user = auth_user();

    return is_array($user) ? (int) ($user['id_posyandu'] ?? 0) : 0;
}

function request_string(array $source, string $key, int $maxLength = 255): string
{
    $value = $source[$key] ?? '';
    $value = is_string($value) ? trim($value) : '';

    return mb_substr($value, 0, $maxLength);
}

function parse_iso_date(string $value): ?DateTimeImmutable
{
    if ($value === '') {
        return null;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();

    if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return null;
    }

    return $date->format('Y-m-d') === $value ? $date : null;
}

function today_iso(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
}

/**
 * Validasi input formulir balita.
 * KFV: tanggal daftar harus setelah tanggal lahir dan usia maksimal 60 bulan
 * sesuai cakupan MVP/demo.
 */
function validate_child_payload(array $input): array
{
    $errors = [];
    $data = [
        'kode_balita'   => strtoupper(request_string($input, 'kode_balita', 40)),
        'nama_balita'   => request_string($input, 'nama_balita', 100),
        'jenis_kelamin' => request_string($input, 'jenis_kelamin', 1),
        'tgl_lahir'     => request_string($input, 'tgl_lahir', 10),
        'nama_wali'     => request_string($input, 'nama_wali', 100),
        'tgl_daftar'    => request_string($input, 'tgl_daftar', 10),
        'persetujuan'   => isset($input['persetujuan']) && (string) $input['persetujuan'] === '1',
    ];

    if (!preg_match('/^[A-Z0-9][A-Z0-9_-]{2,39}$/', $data['kode_balita'])) {
        $errors['kode_balita'] = 'Kode balita harus 3–40 karakter: huruf kapital, angka, _ atau -.';
    }

    if (mb_strlen($data['nama_balita']) < 2) {
        $errors['nama_balita'] = 'Nama balita minimal 2 karakter.';
    }

    if (!in_array($data['jenis_kelamin'], ['L', 'P'], true)) {
        $errors['jenis_kelamin'] = 'Pilih jenis kelamin L atau P.';
    }

    if (!in_array($data['persetujuan'], [true, false], true)) {
        $errors['persetujuan'] = 'Status persetujuan tidak valid.';
    }

    $birthDate = parse_iso_date($data['tgl_lahir']);
    $registerDate = parse_iso_date($data['tgl_daftar'] === '' ? today_iso() : $data['tgl_daftar']);
    $today = parse_iso_date(today_iso());

    if ($registerDate !== null) {
        $data['tgl_daftar'] = $registerDate->format('Y-m-d');
    }

    if ($birthDate === null) {
        $errors['tgl_lahir'] = 'Tanggal lahir tidak valid.';
    }

    if ($registerDate === null) {
        $errors['tgl_daftar'] = 'Tanggal daftar tidak valid.';
    }

    if ($today !== null && $registerDate !== null && $registerDate > $today) {
        $errors['tgl_daftar'] = 'Tanggal daftar tidak boleh berada di masa depan.';
    }

    if ($birthDate !== null && $registerDate !== null) {
        if ($registerDate < $birthDate) {
            $errors['tgl_daftar'] = 'Tanggal daftar harus setelah tanggal lahir.';
        }

        $age = $birthDate->diff($registerDate);
        $ageInMonths = ((int) $age->y * 12) + (int) $age->m;
        if ($ageInMonths > 60) {
            $errors['tgl_lahir'] = 'Cakupan MVP hanya balita usia 0–60 bulan.';
        }
    }

    if (mb_strlen($data['nama_wali']) < 2) {
        $errors['nama_wali'] = 'Nama wali minimal 2 karakter.';
    }

    return ['data' => $data, 'errors' => $errors];
}

function find_child_for_user(int $childId): ?array
{
    if ($childId < 1 || current_posyandu_id() < 1) {
        return null;
    }

    $statement = db()->prepare(
        'SELECT id_balita, id_posyandu, kode_balita, nama_balita, jenis_kelamin,
                tgl_lahir, nama_wali, tgl_daftar, is_active, created_at, updated_at
         FROM data_balita
         WHERE id_balita = :id_balita AND id_posyandu = :id_posyandu
         LIMIT 1'
    );
    $statement->execute([
        'id_balita'   => $childId,
        'id_posyandu' => current_posyandu_id(),
    ]);
    $child = $statement->fetch();

    return is_array($child) ? $child : null;
}

function child_form_redirect(array $data, array $errors, int $childId = 0): never
{
    $_SESSION['child_form'] = [
        'data'   => $data,
        'errors' => $errors,
    ];

    $location = app_url('modules/balita/form.php');
    if ($childId > 0) {
        $location .= '?id=' . $childId;
    }

    redirect($location);
}

function field_error(array $errors, string $key): ?string
{
    $message = $errors[$key] ?? null;

    return is_string($message) && $message !== '' ? $message : null;
}

function latest_consent_for_child(int $childId): ?array
{
    $statement = db()->prepare(
        'SELECT id_consent, status_persetujuan, tanggal_persetujuan, catatan
         FROM consent_records
         WHERE id_balita = :id_balita
         ORDER BY id_consent DESC
         LIMIT 1'
    );
    $statement->execute(['id_balita' => $childId]);
    $consent = $statement->fetch();

    return is_array($consent) ? $consent : null;
}

function write_audit(string $action, string $entityType, int $entityId, string $detail): void
{
    try {
        $statement = db()->prepare(
            'INSERT INTO audit_logs
                (id_posyandu, id_user, action, entity_type, entity_id, detail)
             VALUES
                (:id_posyandu, :id_user, :action, :entity_type, :entity_id, :detail)'
        );
        $statement->execute([
            'id_posyandu' => current_posyandu_id(),
            'id_user'     => current_user_id() ?: null,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => (string) $entityId,
            'detail'      => mb_substr($detail, 0, 500),
        ]);
    } catch (PDOException $exception) {
        error_log('Gagal menulis audit log: ' . $exception->getMessage());
    }
}