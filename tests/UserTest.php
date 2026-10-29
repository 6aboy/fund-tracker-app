<?php
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/User.php';
$pdo = (new Database())->getConnection();
$model = new User($pdo);
foreach (['gabo', 'niko'] as $name) {
    $stored = $model->findByUsername($name);
    if (!$stored || $stored['password_hash'] === 'admin-pass' || !password_verify('admin-pass', $stored['password_hash'])) throw new RuntimeException('Password hashing failed');
    $user = $model->authenticate($name, 'admin-pass');
    if (!$user || isset($user['password_hash'])) throw new RuntimeException('Authentication failed or exposed hash');
    if ($model->authenticate($name, 'incorrect')) throw new RuntimeException('Incorrect password accepted');
}
if ($model->authenticate("' OR 1=1 --", 'admin-pass')) throw new RuntimeException('Unexpected user authenticated');
echo "User authentication checks passed\n";
