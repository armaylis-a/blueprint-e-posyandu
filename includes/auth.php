<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    $stored = $_SESSION['_csrf_token'] ?? '';

    return is_string($token)
        && is_string($stored)
        && $stored !== ''
        && hash_equals($stored, $token);
}

function auth_user(): ?array
{
    return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])
        ? $_SESSION['auth_user']
        : null;
}

function auth_check(): bool
{
    $user = auth_user();
    if ($user === null) {
        return false;
    }

    $lastActivity = (int) ($_SESSION['_last_activity'] ?? time());
    if ((time() - $lastActivity) > 1800) {
        auth_logout();
        return false;
    }

    $_SESSION['_last_activity'] = time();

    return true;
}

function auth_require(): void
{
    if (!auth_check()) {
        set_flash('error', 'Silakan login untuk melanjutkan.');
        redirect('login.php');
    }
}

function auth_require_role(string ...$allowedRoles): void
{
    auth_require();
    $user = auth_user();
    $role = is_array($user) ? (string) ($user['role'] ?? '') : '';

    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

/**
 * Login demo menggunakan prepared statement dan password_hash.
 * Rate limit di MVP ini masih per-session; production perlu rate limit
 * yang dibagikan di server/cache.
 */
function auth_login(string $username, string $password): array
{
    $now = time();
    $attempts = $_SESSION['_login_attempts'] ?? [
        'count'   => 0,
        'started' => $now,
    ];

    if (!is_array($attempts) || ($now - (int) ($attempts['started'] ?? 0)) > 300) {
        $attempts = [
            'count'   => 0,
            'started' => $now,
        ];
    }

    if ((int) ($attempts['count'] ?? 0) >= 5) {
        return [
            'ok'    => false,
            'error' => 'Terlalu banyak percobaan login. Coba lagi dalam 5 menit.',
        ];
    }

    $username = trim($username);
    $statement = db()->prepare(
        'SELECT id_user, id_posyandu, username, password_hash, nama_lengkap, role
         FROM users
         WHERE username = :username AND is_active = 1
         LIMIT 1'
    );
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        $attempts['count'] = (int) ($attempts['count'] ?? 0) + 1;
        $_SESSION['_login_attempts'] = $attempts;

        return [
            'ok'    => false,
            'error' => 'Username atau password tidak sesuai.',
        ];
    }

    if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $rehash = db()->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE id_user = :id_user'
        );
        $rehash->execute([
            'password_hash' => $newHash,
            'id_user'       => (int) $user['id_user'],
        ]);
    }

    $lastLogin = db()->prepare(
        'UPDATE users SET last_login_at = NOW() WHERE id_user = :id_user'
    );
    $lastLogin->execute(['id_user' => (int) $user['id_user']]);

    $audit = db()->prepare(
        'INSERT INTO audit_logs (id_posyandu, id_user, action, entity_type, entity_id, detail)
         VALUES (:id_posyandu, :id_user, :action, :entity_type, :entity_id, :detail)'
    );
    $audit->execute([
        'id_posyandu' => (int) $user['id_posyandu'],
        'id_user'     => (int) $user['id_user'],
        'action'      => 'login',
        'entity_type' => 'user',
        'entity_id'   => (string) $user['id_user'],
        'detail'      => 'Login berhasil.',
    ]);

    unset($_SESSION['_login_attempts']);
    session_regenerate_id(true);

    $_SESSION['auth_user'] = [
        'id_user'       => (int) $user['id_user'],
        'id_posyandu'   => (int) $user['id_posyandu'],
        'username'      => (string) $user['username'],
        'nama_lengkap'  => (string) $user['nama_lengkap'],
        'role'          => (string) $user['role'],
        'login_at'      => date('c'),
    ];
    $_SESSION['_last_activity'] = time();

    return ['ok' => true];
}

function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}
