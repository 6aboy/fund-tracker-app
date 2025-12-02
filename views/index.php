<?php include '../database/database.php'; ?>
<?php include '../layout/header.php'; ?>
<?php require '../models/Transactions.php'; ?>

<?php
$db = new Database();
$conn = $db->getConnection();

Transactions::setConnection($conn);
$transaction = Transactions::all();       // fetch all records
?>

<div class='container mt-5'>

    <div class="d-flex justify-content-end mb-3">
        <a href="create.php" class="btn btn-primary">Create a Transaction</a>
    </div>
    <div class="p-4 bg-white rounded-4 shadow-lg border border-light" style="overflow-x:auto;">
        <div class="d-flex justify-content-center mb-3">
            <h1>Fund Tracker</h1>
        </div>
        <table id="transactionsTable" class="table table-hover table-bordered align-middle text-center mb-0">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Date Created</th>
                <th>Action</th>
            </tr>
            <?php if (!empty($transaction)): ?>
                <?php foreach ($transaction as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row->id) ?></td>
                        <td><?= htmlspecialchars($row->name) ?></td>
                        <td><?= htmlspecialchars($row->category) ?></td>
                        <?php
                        // show a negative sign if category is "expense"
                        if ($row->category != 'expense') {
                            $sign = '+';
                        } else {
                            $sign = '-';
                        }
                        ?>
                        <td><?= htmlspecialchars($sign . '₱' . $row->amount) ?></td>
                        <td><?= date('F j, Y', strtotime($row->date_created) ?? '') ?></td>
                        <td>
                            <a href="destroy.php?id=<?= htmlspecialchars($row->id) ?>" class="btn btn-danger delete-btn"
                                data-href="destroy.php?id=<?= htmlspecialchars($row->id) ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No transactions found</td>
                </tr>
            <?php endif; ?>
        </table>

    </div>

    <!-- include SweetAlert2 and confirm-delete script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include '../layout/footer.php'; ?>