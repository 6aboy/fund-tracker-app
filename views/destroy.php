<?php
require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../models/Transactions.php';

// initialize DB + model connection
$db = new Database();
$conn = $db->getConnection();
Transactions::setConnection($conn);

// NOTE: you requested GET-based deletion. This is implemented below.
// Validate id from GET and perform delete, then redirect back to index.php.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    header('Location: index.php');
    exit;
}

$transaction = Transactions::find($id);
if ($transaction) {
    // call model's delete method (should return boolean)
    $transaction->delete();
}

// always redirect back (no UI feedback)
header('Location: ../index.php');
exit;
?>