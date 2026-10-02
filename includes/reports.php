<?php

declare(strict_types=1);

function report_month_bounds(string $month): array
{
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
        $month = date('Y-m');
    }

    $start = $month . '-01';
    $timestamp = strtotime($start);

    if ($timestamp === false) {
        $month = date('Y-m');
        $start = $month . '-01';
        $timestamp = strtotime($start);
    }

    return [
        'month' => $month,
        'start' => $start,
        'end'   => date('Y-m-t', (int) $timestamp),
    ];
}

function report_month_label(string $month): string
{
    $timestamp = strtotime($month . '-01');
    if ($timestamp === false) {
        return $month;
    }

    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    return $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

function fetch_monthly_report(string $month): array
{
    $bounds = report_month_bounds($month);

    $statement = db()->prepare(
        'SELECT t.id_timbangan, t.tgl_periksa, t.umur_bulan,
                t.berat_badan_kg, t.tinggi_badan_cm, t.jenis_pengukuran,
                b.id_balita, b.kode_balita, b.nama_balita, b.jenis_kelamin,
                b.nama_wali,
                h.status_stunting, h.status_gizi, h.perlu_tinjauan, h.review_status
         FROM timbangan_bulanan t
         INNER JOIN data_balita b ON b.id_balita = t.id_balita
         LEFT JOIN hasil_antropometri h ON h.id_timbangan = t.id_timbangan
         WHERE b.id_posyandu = :id_posyandu
           AND t.deleted_at IS NULL
           AND t.tgl_periksa BETWEEN :start_date AND :end_date
         ORDER BY t.tgl_periksa ASC, b.nama_balita ASC, b.kode_balita ASC'
    );
    $statement->execute([
        'id_posyandu' => current_posyandu_id(),
        'start_date'  => $bounds['start'],
        'end_date'    => $bounds['end'],
    ]);
    $rows = $statement->fetchAll();

    $summary = [
        'total'          => count($rows),
        'children'       => count(array_unique(array_map(
            static fn (array $row): string => (string) $row['id_balita'],
            $rows
        ))),
        'indication'     => 0,
        'normal'         => 0,
        'uncalculated'   => 0,
    ];

    foreach ($rows as $row) {
        $status = (string) ($row['status_stunting'] ?? '');

        if ($status === 'demo_indikasi_stunting') {
            $summary['indication']++;
        } elseif ($status === 'demo_tidak_ada_indikasi') {
            $summary['normal']++;
        } else {
            $summary['uncalculated']++;
        }
    }

    return [
        'month'  => $bounds['month'],
        'label'  => report_month_label($bounds['month']),
        'start'  => $bounds['start'],
        'end'    => $bounds['end'],
        'rows'   => $rows,
        'summary' => $summary,
    ];
}