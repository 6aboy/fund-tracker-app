<?php
require_once __DIR__ . '/../auth/session.php';
require_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit; }
verify_csrf();
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/Transactions.php';
Transactions::setConnection((new Database())->getConnection());
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id && $id > 0) {
    $transaction = Transactions::find($id);
    if ($transaction) $transaction->delete();
}
header('Location: index.php');
exit;
