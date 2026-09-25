<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (auth_check()) {
    redirect('index.php');
}

$error = null;
$username = '';

if (is_post()) {
    $usernameValue = $_POST['username'] ?? '';
    $passwordValue = $_POST['password'] ?? '';
    $username = is_string($usernameValue) ? trim($usernameValue) : '';
    $password = is_string($passwordValue) ? $passwordValue : '';
    $csrfValue = $_POST['csrf_token'] ?? null;

    if (!is_string($csrfValue) || !csrf_verify($csrfValue)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } else {
        $result = auth_login($username, $password);

        if ($result['ok'] === true) {
            redirect('index.php');
        }

        $error = (string) ($result['error'] ?? 'Login gagal.');
    }
}

$pageTitle = 'Login';
$showNavigation = false;
$bodyClass = 'login-page';
require __DIR__ . '/partials/header.php';
?>

<section class="login-shell">
    <div class="login-card">
        <div class="login-heading">
            <span class="brand-mark brand-mark-large">EP</span>
            <div>
                <p class="eyebrow">MVP AKADEMIK</p>
                <h1>Masuk E-Posyandu</h1>
                <p class="muted">Akses demo untuk modul Posyandu.</p>
            </div>
        </div>

        <div class="alert alert-info demo-notice" role="note">
            <strong>Data demo.</strong> Gunakan akun lokal berikut; jangan gunakan data pribadi nyata.
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" class="stack-form" autocomplete="on">
            <?= csrf_field() ?>

            <label for="username">Username</label>
            <input
                id="username"
                name="username"
                type="text"
                value="<?= e($username) ?>"
                maxlength="60"
                autocomplete="username"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                maxlength="255"
                autocomplete="current-password"
                required
            >

            <button type="submit" class="button button-primary button-block">Login</button>
        </form>

        <details class="demo-accounts">
            <summary>Akun demo</summary>
            <ul>
                <li><code>kader.demo</code> / <code>DemoKader123!</code></li>
                <li><code>supervisor.demo</code> / <code>DemoSupervisor123!</code></li>
            </ul>
        </details>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
