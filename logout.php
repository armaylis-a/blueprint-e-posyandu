<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!is_post()) {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}

if (!auth_check()) {
    redirect('login.php');
}

$csrfValue = $_POST['csrf_token'] ?? null;
if (!is_string($csrfValue) || !csrf_verify($csrfValue)) {
    set_flash('error', 'Sesi logout tidak valid. Silakan coba lagi.');
    redirect('index.php');
}

auth_logout();
redirect('login.php');
