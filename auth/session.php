<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/fund_tracker/']);
    session_start();
}
function csrf_token(): string {
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403); exit('Invalid form token. Refresh the page and try again.');
    }
}
function require_auth(): void {
    if (empty($_SESSION['user'])) {
        header('Location: /fund_tracker/login.php'); exit;
    }
    header('Cache-Control: no-store');
}
