<?php
require_once __DIR__ . '/../auth/session.php';
require_auth();

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../models/Transactions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_lifetime' => 86400]);
}

$db = new Database();
$conn = $db->getConnection();
Transactions::setConnection($conn);

// Handle POST first so we can redirect (prevents "headers already sent")
// Handle POST first so we can redirect (prevents "headers already sent")
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Basic sanitization/validation
    $name = trim($_POST['name'] ?? '');
    $amountRaw = $_POST['amount'] ?? '';
    $category = strtolower(trim($_POST['category'] ?? ''));

    $amount = filter_var($amountRaw, FILTER_VALIDATE_FLOAT);
    $validCategory = in_array($category, ['deposit', 'expense'], true);

    // If invalid input
    if ($name === '' || $amount === false || !is_finite($amount) || $amount <= 0 || !$validCategory) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Invalid input. Please check your fields.'
        ];
        header('Location: create.php');
        exit;
    }

    // Create transaction using model
    $transaction = Transactions::create([
        'name' => $name,
        'amount' => $amount,
        'category' => $category
    ]);

    if ($transaction) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Transaction added successfully!'
        ];
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Failed to save transaction.'
        ];
        header('Location: create.php');
        exit;
    }
}

// If GET: render page (so footer/header load normally)
include __DIR__ . '/../layout/header.php';

// If you have a separate create.php with the form, you can redirect or include it here.
// For minimal flow just include footer so any flashes will render there.
include __DIR__ . '/../layout/footer.php';
?>
