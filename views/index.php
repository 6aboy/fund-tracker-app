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
$transaction = Transactions::all();       // fetch all records
?>

<div class='container mt-5'>

    <div class="d-flex justify-content-end mb-3 gap-2">
        <a href="create.php" class="btn btn-primary">Create a Transaction</a>
        <a href="analytics.php" class="btn btn-info">📊 Analytics & Predictions</a>
    </div>
    <div class="p-4 bg-white rounded-4 shadow-lg border border-light" style="overflow-x:auto;">
        <div class="d-flex justify-content-center mb-3">
            <h1>Transactions</h1>
        </div>
        <table id="transactionsTable" class="table table-hover table-bordered align-middle text-center mb-0">
            <thead><tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Date Created</th>
                <th class="no-export">Action</th>
            </tr></thead><tbody>
            <?php if (!empty($transaction)): ?>
                <?php foreach ($transaction as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row->id) ?></td>
                        <td><?= htmlspecialchars($row->name) ?></td>
                        <td><span class="type-badge <?= strtolower(trim($row->category ?? '')) === 'deposit' ? 'type-deposit' : 'type-expense' ?>"><?= htmlspecialchars(ucfirst($row->category)) ?></span></td>
                        <?php
                        // show a negative sign if category is "expense"
                        if ($row->category != 'expense') {
                            $sign = '+';
                        } else {
                            $sign = '-';
                        }
                        ?>
                        <td data-order="<?= (float)$row->amount * ($sign === '-' ? -1 : 1) ?>"><?= htmlspecialchars($sign . '₱' . $row->amount) ?></td>
                        <td data-order="<?= htmlspecialchars($row->date_created ?? '') ?>"><?= date('F j, Y', strtotime($row->date_created ?? '')) ?></td>
                        <td>
                            <form action="destroy.php" method="post" class="delete-transaction">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$row->id ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- include SweetAlert2 and confirm-delete script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../layout/footer.php'; ?>