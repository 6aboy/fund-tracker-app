<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Run with: php database/seed_demo.php. Each sample is clearly labeled and inserted only once.
require_once __DIR__ . '/Database.php';
$pdo = (new Database())->getConnection();
$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
$samples = [
 [28, 'Opening funds', 'deposit', 18000], [24, 'Groceries', 'expense', 1850.75],
 [21, 'Internet bill', 'expense', 1499], [18, 'Freelance project', 'deposit', 6500],
 [15, 'Electricity', 'expense', 2175.50], [12, 'Transport', 'expense', 420],
 [9, 'Coffee and lunch', 'expense', 285], [6, 'Salary', 'deposit', 12500],
 [6, 'Weekly groceries', 'expense', 1680.50], [5, 'Commute', 'expense', 180],
 [4, 'Dining out', 'expense', 650], [3, 'Side project', 'deposit', 2200],
 [3, 'Mobile load', 'expense', 299], [2, 'Household supplies', 'expense', 740.25],
 [1, 'Fuel', 'expense', 1200], [0, 'Refund', 'deposit', 850], [0, 'Lunch', 'expense', 245.50],
];
$exists = $pdo->prepare('SELECT COUNT(*) FROM transaction WHERE name = ?');
$insert = $pdo->prepare('INSERT INTO transaction (name, category, amount, date_created) VALUES (?, ?, ?, ?)');
$pdo->beginTransaction();
try {
    $count = 0;
    foreach ($samples as [$days, $name, $category, $amount]) {
        $name = '[DEMO] ' . $name;
        $exists->execute([$name]);
        if ($exists->fetchColumn()) continue;
        $insert->execute([$name, $category, $amount, $today->modify("-$days days")->format('Y-m-d') . ' 12:00:00']);
        $count++;
    }
    $pdo->commit();
    echo "Added $count labeled demo transactions.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
