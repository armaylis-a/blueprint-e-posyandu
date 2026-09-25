<?php

declare(strict_types=1);

/**
 * Koneksi database untuk MVP akademik.
 *
 * Nilai default(Local XAMPP/MariaDB) hanya untuk pengembangan.
 * Untuk hosting, set variabel lingkungan DB_* dan jangan simpan
 * kredensial production di dalam file ini.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'e_posyandu_mvp';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD');

    if ($password === false) {
        $password = '';
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $name
    );

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $exception) {
        error_log('Koneksi database gagal: ' . $exception->getMessage());
        http_response_code(500);
        exit('Aplikasi tidak dapat terhubung ke database demo.');
    }

    return $pdo;
}
