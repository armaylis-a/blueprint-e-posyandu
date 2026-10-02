<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'E-Posyandu MVP';
$showNavigation = $showNavigation ?? false;
$currentUser = auth_user();
$flash = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="E-Posyandu MVP Akademik dengan data sintetis">
    <meta name="sw-path" content="<?= e(app_url('sw.js')) ?>">
    <link rel="manifest" href="<?= e(app_url('manifest.json')) ?>">
    <title><?= e($pageTitle) ?> | E-Posyandu</title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<div class="app-shell">
    <?php if ($showNavigation): ?>
        <header class="topbar">
            <div class="topbar-inner">
                <a class="brand" href="<?= e(app_url('index.php')) ?>" aria-label="Beranda E-Posyandu">
                    <span class="brand-mark">EP</span>
                    <span>
                        <strong>E-Posyandu</strong>
                        <small>MVP Akademik · Data Demo</small>
                    </span>
                </a>
                <?php if ($currentUser !== null): ?>
                    <div class="user-box">
                        <span><?= e($currentUser['nama_lengkap'] ?? 'Pengguna') ?></span>
                        <small><?= e($currentUser['role'] ?? '') ?></small>
                    </div>
                    <form method="post" action="<?= e(app_url('logout.php')) ?>" class="logout-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="button button-secondary button-small">Logout</button>
                    </form>
                <?php endif; ?>
            </div>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a href="<?= e(app_url('index.php')) ?>">Dashboard</a>
                <a href="<?= e(app_url('modules/balita/index.php')) ?>">Data Balita</a>
                <a href="<?= e(app_url('modules/timbangan/index.php')) ?>">Timbangan</a>
                <a href="<?= e(app_url('modules/laporan/index.php')) ?>">Laporan</a>
            </nav>
        </header>
    <?php endif; ?>

    <main class="page-container">
        <?php if (is_array($flash)): ?>
            <div class="alert alert-<?= e($flash['type'] ?? 'info') ?>" role="status">
                <?= e($flash['message'] ?? '') ?>
            </div>
        <?php endif; ?>
