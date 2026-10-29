<?php
require_once __DIR__ . '/auth/session.php';
require_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit; }
verify_csrf();
$_SESSION = [];
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/fund_tracker/', 'httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
session_destroy();
header('Location: /fund_tracker/login.php');
exit;
