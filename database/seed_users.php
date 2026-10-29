<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/Database.php';
$pdo = (new Database())->getConnection();
$pdo->exec('CREATE TABLE IF NOT EXISTS users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(64) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
$stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE username = VALUES(username)');
foreach (['gabo', 'niko'] as $username) {
    $stmt->execute([$username, password_hash('admin-pass', PASSWORD_DEFAULT)]);
}
echo "Predefined accounts ready (existing passwords preserved).\n";
