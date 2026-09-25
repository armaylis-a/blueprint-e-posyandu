<?php

declare(strict_types=1);

/**
 * Konfigurasi session untuk aplikasi demo lokal.
 * Pada production HTTPS, secure_cookie harus aktif.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $secureCookie = filter_var(getenv('SESSION_SECURE') ?: 'false', FILTER_VALIDATE_BOOL);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.gc_maxlifetime', '1800');

    session_name('EPOSYANDU_SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps ? true : (bool) $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
