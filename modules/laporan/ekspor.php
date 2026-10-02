<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
auth_require();

$month = request_string($_GET, 'bulan', 7);

try {
    $report = fetch_monthly_report($month);
} catch (PDOException $exception) {
    error_log('Gagal mengekspor laporan: ' . $exception->getMessage());
    http_response_code(500);
    exit('Laporan tidak dapat diekspor.');
}

function csv_safe(string $value): string
{
    return preg_match('/^[=+\-@]/', $value) === 1 ? "'" . $value : $value;
}

$filename = 'laporan-posyandu-' . $report['month'] . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$output = fopen('php://output', 'wb');
if ($output === false) {
    exit('Output tidak dapat dibuat.');
}

// BOM agar Excel di Windows membaca karakter UTF-8 dengan benar.
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, [
    'Kode Balita',
    'Nama Balita',
    'Jenis Kelamin',
    'Nama Wali',
    'Tanggal Periksa',
    'Usia Bulan',
    'Berat kg',
    'Tinggi cm',
    'Metode Ukur',
    'Status Demo',
    'Status Gizi',
]);

foreach ($report['rows'] as $row) {
    fputcsv($output, [
        csv_safe((string) $row['kode_balita']),
        csv_safe((string) $row['nama_balita']),
        csv_safe((string) $row['jenis_kelamin']),
        csv_safe((string) $row['nama_wali']),
        csv_safe((string) $row['tgl_periksa']),
        csv_safe((string) $row['umur_bulan']),
        csv_safe((string) $row['berat_badan_kg']),
        csv_safe((string) $row['tinggi_badan_cm']),
        csv_safe((string) $row['jenis_pengukuran']),
        csv_safe((string) ($row['status_stunting'] ?? '-')),
        csv_safe((string) ($row['status_gizi'] ?? '-')),
    ]);
}

fclose($output);