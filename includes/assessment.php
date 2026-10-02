<?php

declare(strict_types=1);

/**
 * Utilitas perhitungan usia dan assessor demo.
 *
 * Assessor di bawah ini sengaja hanya mengimplementasikan threshold IF-ELSE
 * dari blueprint. Ini BUKAN z-score WHO/Kemenkes dan tidak boleh dipakai
 * untuk diagnosis atau keputusan klinis.
 */
function calculate_age_details(string $birthDate, string $measureDate): ?array
{
    $birth = parse_iso_date($birthDate);
    $measurement = parse_iso_date($measureDate);

    if ($birth === null || $measurement === null || $measurement < $birth) {
        return null;
    }

    $difference = $birth->diff($measurement);
    $wholeMonths = ((int) $difference->y * 12) + (int) $difference->m;
    $days = (int) $difference->format('%a');
    $decimalMonths = round($wholeMonths + ($days / 30.4375), 2);

    return [
        'whole_months'  => $wholeMonths,
        'decimal_months' => $decimalMonths,
    ];
}

function measurement_method_for_age(int $wholeMonths): string
{
    return $wholeMonths < 24 ? 'panjang_baring' : 'tinggi_berdiri';
}

function demo_height_threshold(float $ageMonths): float
{
    if ($ageMonths <= 6) {
        return 60.0;
    }

    if ($ageMonths <= 12) {
        return 71.0;
    }

    if ($ageMonths <= 24) {
        return 80.0;
    }

    return 88.0;
}

function demo_growth_assessment(float $ageMonths, float $heightCm): array
{
    $threshold = demo_height_threshold($ageMonths);
    $isIndication = $heightCm < $threshold;

    return [
        'status_stunting' => $isIndication ? 'demo_indikasi_stunting' : 'demo_tidak_ada_indikasi',
        'status_gizi'     => $isIndication ? 'demo_perlu_evaluasi' : 'demo_normal',
        'perlu_tinjauan'  => $isIndication ? 1 : 0,
        'threshold_cm'    => $threshold,
        'catatan'         => 'Hasil simulasi IF-ELSE. Bukan z-score klinis atau diagnosis.',
    ];
}

function validate_measurement_payload(array $input): array
{
    $errors = [];
    $data = [
        'id_balita'     => request_string($input, 'id_balita', 12),
        'tgl_periksa'   => request_string($input, 'tgl_periksa', 10),
        'berat_badan'   => str_replace(',', '.', request_string($input, 'berat_badan', 10)),
        'tinggi_badan'  => str_replace(',', '.', request_string($input, 'tinggi_badan', 10)),
        'catatan'       => request_string($input, 'catatan', 255),
    ];

    if (!ctype_digit($data['id_balita']) || (int) $data['id_balita'] < 1) {
        $errors['id_balita'] = 'Pilih balita yang valid.';
    }

    $child = ctype_digit($data['id_balita'])
        ? find_child_for_user((int) $data['id_balita'])
        : null;

    if ($child === null) {
        $errors['id_balita'] = 'Data balita tidak ditemukan atau tidak dapat diakses.';
    }

    $measurementDate = parse_iso_date($data['tgl_periksa']);
    $today = parse_iso_date(today_iso());

    if ($measurementDate === null) {
        $errors['tgl_periksa'] = 'Tanggal pemeriksaan tidak valid.';
    }

    if ($today !== null && $measurementDate !== null && $measurementDate > $today) {
        $errors['tgl_periksa'] = 'Tanggal pemeriksaan tidak boleh berada di masa depan.';
    }

    $weight = is_numeric($data['berat_badan']) ? (float) $data['berat_badan'] : null;
    $height = is_numeric($data['tinggi_badan']) ? (float) $data['tinggi_badan'] : null;

    if ($weight === null || $weight <= 0 || $weight > 200) {
        $errors['berat_badan'] = 'Berat badan harus angka 0–200 kg.';
    }

    if ($height === null || $height <= 0 || $height > 200) {
        $errors['tinggi_badan'] = 'Tinggi badan harus angka 0–200 cm.';
    }

    $ageDetails = null;
    if ($child !== null && $measurementDate !== null) {
        $ageDetails = calculate_age_details((string) $child['tgl_lahir'], $data['tgl_periksa']);

        if ($ageDetails === null) {
            $errors['tgl_periksa'] = 'Tanggal pemeriksaan tidak boleh sebelum tanggal lahir.';
        } elseif ($ageDetails['whole_months'] > 60 && !isset($errors['tgl_periksa'])) {
            $errors['tgl_periksa'] = 'Cakupan MVP hanya mengukur balita usia 0–60 bulan.';
        }
    }

    $assessment = null;
    if ($ageDetails !== null && $height !== null && $height > 0) {
        $assessment = demo_growth_assessment((float) $ageDetails['decimal_months'], $height);
        $data['umur_bulan'] = $ageDetails['decimal_months'];
        $data['jenis_pengukuran'] = measurement_method_for_age((int) $ageDetails['whole_months']);
    } else {
        $data['umur_bulan'] = 0.0;
        $data['jenis_pengukuran'] = 'panjang_baring';
    }

    return [
        'data'       => $data,
        'errors'     => $errors,
        'child'      => $child,
        'assessment' => $assessment,
    ];
}

function measurement_form_redirect(array $data, array $errors): never
{
    $_SESSION['measurement_form'] = [
        'data'   => $data,
        'errors' => $errors,
    ];

    redirect(app_url('modules/timbangan/form.php'));
}

function find_demo_standard(): ?array
{
    $statement = db()->prepare(
        'SELECT id_standar, kode_standar, versi_standar, nama_standar
         FROM standar_antropometri
         WHERE kode_standar = :kode_standar
           AND versi_standar = :versi_standar
           AND is_demo = 1
         LIMIT 1'
    );
    $statement->execute([
        'kode_standar'  => 'DEMO-IFELSE',
        'versi_standar' => '1.0',
    ]);
    $standard = $statement->fetch();

    return is_array($standard) ? $standard : null;
}