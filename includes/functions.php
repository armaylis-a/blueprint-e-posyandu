<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

/**
 * Mengembalikan prefix relatif dari folder aplikasi.
 * Dari root -> '', dari /modules/balita/ -> '../../'.
 */
function base_url(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $segments = explode('/', trim($script, '/'));
    $moduleIndex = array_search('modules', $segments, true);

    if ($moduleIndex === false) {
        return '';
    }

    $depthAfterModules = count($segments) - $moduleIndex - 1;

    return str_repeat('../', max(0, $depthAfterModules));
}

function app_url(string $path = ''): string
{
    return base_url() . ltrim($path, '/');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function format_date_id(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? $date : date('d-m-Y', $timestamp);
}
