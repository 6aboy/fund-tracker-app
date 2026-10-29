<?php
require_once __DIR__ . '/../auth/session.php';
require_auth();
 include '../database/database.php'; ?>
<?php include '../layout/header.php'; ?>
<?php require '../models/Transactions.php'; ?>

<?php
$db = new Database();
$conn = $db->getConnection();

Transactions::setConnection($conn);
?>

<div class='container mt-5'>
    <div class="p-4 bg-white rounded-4 shadow-lg border border-light" style="overflow-x:auto; max-width:650px; margin:auto;">
        <div class="d-flex justify-content-center mb-3">
            <h1>Add transaction</h1>
        </div>
        <form id="transactionForm" action="store.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" autofocus placeholder="Enter Name..." class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label" style="color:#112D4E;">Category</label>

                <select class="form-select" id="category" name="category" required>
                    <option value="" selected disabled hidden>Select the category...</option>
                    <option value="expense">Expense</option>
                    <option value="deposit">Deposit</option>
                </select>

            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" step="0.01" min="0.01" class="form-control" autofocus id="amount" name="amount" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="index.php" class="btn btn-dark">Back</a>
        </form>
    </div>

<?php include '../layout/footer.php'; ?>
