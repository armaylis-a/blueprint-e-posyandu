<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$checks = [];
$addCheck = static function (string $name, bool $passed, string $detail = '') use (&$checks): void {
    $checks[] = [
        'name'   => $name,
        'passed' => $passed,
        'detail' => $detail,
    ];
};

try {
    $database = db()->query('SELECT DATABASE() AS nama')->fetch();
    $addCheck('Koneksi database', is_array($database), (string) ($database['nama'] ?? ''));

    $row = db()->query(
        'SELECT
            (SELECT COUNT(*) FROM posyandu WHERE is_active = 1) AS posyandu,
            (SELECT COUNT(*) FROM users WHERE is_active = 1) AS users,
            (SELECT COUNT(*) FROM data_balita WHERE is_active = 1) AS balita,
            (SELECT COUNT(*) FROM timbangan_bulanan WHERE deleted_at IS NULL) AS timbangan,
            (SELECT COUNT(*) FROM hasil_antropometri) AS hasil'
    )->fetch() ?: [];
    $addCheck('Seed minimal tersedia', (int) ($row['posyandu'] ?? 0) >= 1, json_encode($row, JSON_UNESCAPED_UNICODE));
    $addCheck('Balita sintetis tersedia', (int) ($row['balita'] ?? 0) >= 8, 'minimal 8 data demo');

    $hashCount = (int) db()->query(
        'SELECT COUNT(*) FROM users WHERE password_hash LIKE \'$2y$%\' OR password_hash LIKE \'$2b$%\''
    )->fetchColumn();
    $addCheck('Password tersimpan sebagai hash', $hashCount >= 2, $hashCount . ' akun terverifikasi');

    $standard = db()->query(
        "SELECT COUNT(*) FROM standar_antropometri WHERE kode_standar = 'DEMO-IFELSE' AND is_demo = 1"
    )->fetchColumn();
    $addCheck('Standar demo tersedia', (int) $standard >= 1);

    $orphans = (int) db()->query(
        'SELECT COUNT(*)
         FROM hasil_antropometri h
         LEFT JOIN timbangan_bulanan t ON t.id_timbangan = h.id_timbangan
         WHERE t.id_timbangan IS NULL'
    )->fetchColumn();
    $addCheck('Hasil tidak memiliki measurement orphan', $orphans === 0, $orphans . ' orphan');

    $demoChildren = (int) db()->query(
        "SELECT COUNT(*) FROM data_balita WHERE nama_balita LIKE 'Anak Demo%'"
    )->fetchColumn();
    $addCheck('Nama balita contoh terdeteksi', $demoChildren >= 8, $demoChildren . ' nama demo');
} catch (Throwable $exception) {
    $addCheck('Eksekusi validasi', false, $exception->getMessage());
}

$failed = 0;
foreach ($checks as $check) {
    $status = $check['passed'] ? 'PASS' : 'FAIL';
    echo $status . ' - ' . $check['name'];

    if ($check['detail'] !== '') {
        echo ' (' . $check['detail'] . ')';
    }

    echo PHP_EOL;

    if (!$check['passed']) {
        $failed++;
    }
}

echo PHP_EOL . 'Total: ' . count($checks) . ' | Gagal: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);