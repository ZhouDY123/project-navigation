<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function admin_password_hash(): string
{
    $stmt = db()->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute(['admin_password_hash']);
    return (string)($stmt->fetchColumn() ?: ADMIN_PASSWORD_HASH);
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('devhub_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function is_admin(): bool
{
    start_secure_session();
    return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
}

function require_admin_page(): void
{
    if (!is_admin()) {
        $next = rawurlencode(basename((string)($_SERVER['REQUEST_URI'] ?? 'admin.php')));
        header('Location: login.php?next=' . $next);
        exit;
    }
}

function login_admin(string $username, string $password): bool
{
    start_secure_session();
    if (!hash_equals(ADMIN_USERNAME, $username) || !password_verify($password, admin_password_hash())) return false;
    session_regenerate_id(true);
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

function change_admin_password(string $currentPassword, string $newPassword): bool
{
    if (!password_verify($currentPassword, admin_password_hash())) return false;
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = db()->prepare("INSERT INTO settings(key,value,updated_at) VALUES('admin_password_hash',?,CURRENT_TIMESTAMP) ON CONFLICT(key) DO UPDATE SET value=excluded.value,updated_at=CURRENT_TIMESTAMP");
    $stmt->execute([$hash]);
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

function logout_admin(): void
{
    start_secure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function valid_csrf(string $token): bool
{
    start_secure_session();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
