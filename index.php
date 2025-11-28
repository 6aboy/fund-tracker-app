<?php

include 'database/database.php';
include 'models/Transactions.php';
include 'layout/header.php';

$db = new Database();
$conn = $db->getConnection();

Transactions::setConnection($conn);
$transactions = Transactions::all() ?? [];

// Calculate totals
$total_deposits = 0;
$total_expenses = 0;
$daily_deposit = 0;
$daily_expense = 0;
$today = date('Y-m-d');

foreach ($transactions as $t) {
    $amount = (float) ($t->amount ?? 0);
    $category = strtolower($t->category ?? '');

    if ($category === 'deposit') {
        $total_deposits += $amount;
    } else {
        // treat non-deposit as expense (ensure matches your enum values)
        $total_expenses += $amount;
    }

    // Normalize date part only; date_created may be 'YYYY-MM-DD' or include time
    $rowDate = null;
    if (isset($t->date_created)) {
        $rowDate = strtotime($t->date_created) !== false ? date('Y-m-d', strtotime($t->date_created)) : $t->date_created;
    }

    if ($rowDate === $today) {
        if ($category === 'deposit') {
            $daily_deposit += $amount;
        } elseif ($category === 'expense') {
            $daily_expense += $amount;
        }
    }
}

$total_budget = $total_deposits - $total_expenses;
$daily_net = $daily_deposit - $daily_expense;

// Percent: if there was a daily deposit, percent = net / deposit * 100
if ($daily_deposit > 0) {
    $daily_percent = ($daily_net / $daily_deposit) * 100;
} else {
    // if no deposit but there are expenses, show -100% to indicate full negative;
    // if neither, show 0%
    $daily_percent = $daily_expense > 0 ? -100 : 0;
}

function format_currency($n)
{
    return '₱' . number_format((float) $n, 2);
}

// --- prepare 7-day series and category totals for charts ---
$days = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $days[] = $d;
}
$dayLabels = array_map(fn($d) => date('M j', strtotime($d)), $days);
$dailyDeposits = array_fill(0, 7, 0.0);
$dailyExpenses = array_fill(0, 7, 0.0);
$categoryTotals = [];

foreach ($transactions as $t) {
    $amt = (float) ($t->amount ?? 0);
    $cat = strtolower($t->category ?? 'unknown');
    $created = isset($t->date_created) ? (strtotime($t->date_created) !== false ? date('Y-m-d', strtotime($t->date_created)) : null) : null;

    // category totals (for pie)
    if (!isset($categoryTotals[$cat]))
        $categoryTotals[$cat] = 0.0;
    $categoryTotals[$cat] += $amt;

    // daily series
    if ($created !== null) {
        $idx = array_search($created, $days, true);
        if ($idx !== false) {
            if ($cat === 'deposit')
                $dailyDeposits[$idx] += $amt;
            else
                $dailyExpenses[$idx] += $amt;
        }
    }
}

// compute starting balance before the 7-day window
$startDate = $days[0]; // first day in the 7-day window (YYYY-MM-DD)
$starting_balance = 0.0;
foreach ($transactions as $t) {
    $amt = (float) ($t->amount ?? 0);
    $cat = strtolower($t->category ?? 'unknown');
    $created = isset($t->date_created) && strtotime($t->date_created) !== false
        ? date('Y-m-d', strtotime($t->date_created))
        : null;

    // sum everything before the window into starting_balance
    if ($created !== null && $created < $startDate) {
        if ($cat === 'deposit') {
            $starting_balance += $amt;
        } else {
            $starting_balance -= $amt;
        }
    }
}

// build cumulative (running) budget for the 7-day series starting from historical balance
$dailyBudgets = [];
$running = $starting_balance;
for ($i = 0; $i < count($dailyDeposits); $i++) {
    $running += ($dailyDeposits[$i] - $dailyExpenses[$i]);
    $dailyBudgets[$i] = $running;
}
// encode chart series as numeric JSON
$chart_budget_data = json_encode($dailyBudgets, JSON_NUMERIC_CHECK);
$chart_day_labels = json_encode($dayLabels);
$chart_deposit_data = json_encode($dailyDeposits, JSON_NUMERIC_CHECK);
$chart_expense_data = json_encode($dailyExpenses, JSON_NUMERIC_CHECK);

// --- show Deposits vs Expenses only for the pie ---
$chart_cat_labels = json_encode(['Deposits', 'Expenses']);
$chart_cat_values = json_encode([$total_deposits, $total_expenses], JSON_NUMERIC_CHECK);
?>

<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb">
        <h1>Fund Tracker</h1>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-light rounded">
                <strong>Total Budget</strong>
                <div class="h4 mt-1 text-primary"><?= htmlspecialchars(format_currency($total_budget)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded">
                <strong>Total Deposits</strong>
                <div class="h5 mt-2 text-success"><?= htmlspecialchars(format_currency($total_deposits)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded">
                <strong>Total Expenses</strong>
                <div class="h5 mt-2 text-danger"><?= htmlspecialchars(format_currency($total_expenses)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded">
                <strong>Today's Date</strong>
                <div class="h5 mt-2"><?= htmlspecialchars(date('F j, Y')) ?></div>
            </div>
        </div>
    </div>

    <!-- Daily summary: deposit, expense, net, percent -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong>Daily Deposit</strong>
                <div class="h5 mt-2 text-success"><?= htmlspecialchars(format_currency($daily_deposit)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong>Daily Expense</strong>
                <div class="h5 mt-2 text-danger"><?= htmlspecialchars(format_currency($daily_expense)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong>Net Today</strong>
                <div class="h5 mt-2 <?= $daily_net >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= htmlspecialchars(format_currency($daily_net)) ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong>Net vs Deposit</strong>
                <?php $percent_label = ($daily_percent >= 0 ? '+' : '') . number_format($daily_percent, 2) . '%'; ?>
                <div class="h5 mt-2 <?= $daily_percent >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= htmlspecialchars($percent_label) ?>
                </div>
                <small class="text-muted">of today's deposits</small>
            </div>
        </div>
    </div>

    <!-- insert canvases above transactions table -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong>Last 7 days — Deposits vs Expenses (Budget = running total)</strong>
                <canvas id="dailyChart" height="150"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong>Deposits vs Expenses</strong>
                <canvas id="categoryChart" height="150"></canvas>
                <!-- container for category percentages -->
                <div id="categoryPercentList" class="mt-2 small"></div>
            </div>
        </div>
    </div>

    <div class="p-4 bg-white rounded-4 shadow-lg border border-light" style="overflow-x:auto;">
        <table id="transactionsTable" class="table table-hover table-bordered align-middle text-center mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $row): ?>
                        <?php
                            // show negative sign if expense
                            $sign = (strtolower($row->category ?? '') === 'expense') ? '-' : '';
                            $amountFormatted = number_format((float)($row->amount ?? 0), 2);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row->id) ?></td>
                            <td><?= htmlspecialchars($row->name) ?></td>
                            <td><?= htmlspecialchars($row->category) ?></td>
                            <td><?= htmlspecialchars($sign . '₱' . $amountFormatted) ?></td>
                            <td><?= htmlspecialchars(@date('F j, Y', strtotime($row->date_created ?? ''))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No transactions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- include Chart.js and init charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const labels = <?= $chart_day_labels ?>;
        const depositData = <?= $chart_deposit_data ?>;
        const expenseData = <?= $chart_expense_data ?>;
        const catLabels = <?= $chart_cat_labels ?>;
        const catValues = <?= $chart_cat_values ?>;
        const budgetData = <?= $chart_budget_data ?>;

        // line chart for daily deposits vs expenses + running budget
        const ctx = document.getElementById('dailyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Deposits', data: depositData, borderColor: 'rgba(40,167,69,0.9)', backgroundColor: 'rgba(40,167,69,0.12)', tension: 0.2, fill: false },
                    { label: 'Expenses', data: expenseData, borderColor: 'rgba(220,53,69,0.9)', backgroundColor: 'rgba(220,53,69,0.12)', tension: 0.2, fill: false },
                    // Budget line — blue running total
                    {
                        label: 'Budget',
                        data: budgetData,
                        borderColor: 'rgba(54,162,235,0.95)',
                        backgroundColor: 'rgba(54,162,235,0.12)',
                        tension: 0.2,
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                stacked: false,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const val = context.parsed.y;
                                return label + ': ₱' + val.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                            }
                        }
                    }
                }
            }
        });

        // pie chart for Deposits vs Expenses
        const ctx2 = document.getElementById('categoryChart').getContext('2d');
        const colors = ['#28a745', '#dc3545']; // green for deposits, red for expenses
        const categoryChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catValues,
                    backgroundColor: colors.slice(0, (catLabels || []).length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const val = context.parsed || 0;
                                const total = context.chart._metasets[context.datasetIndex].total || categoryChart.data.datasets[0].data.reduce((s,v) => s+v,0);
                                const pct = total ? (val / total * 100).toFixed(2) : '0.00';
                                return label + ': ₱' + Number(val).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Build category percentage text below the chart (use chart's data for accuracy)
        (function renderCategoryPercentList() {
            let listEl = document.getElementById('categoryPercentList');
            if (!listEl) {
                listEl = document.createElement('div');
                listEl.id = 'categoryPercentList';
                listEl.className = 'mt-2 small';
                document.getElementById('categoryChart').parentNode.appendChild(listEl);
            }

            const values = Array.isArray(categoryChart.data.datasets[0].data)
                ? categoryChart.data.datasets[0].data.map(Number)
                : [];
            const total = values.reduce((s, v) => s + (isNaN(v) ? 0 : v), 0);
            if (total === 0) {
                listEl.innerHTML = '<div class="text-muted">No category data</div>';
                return;
            }

            const labelsArr = Array.isArray(categoryChart.data.labels) ? categoryChart.data.labels : [];
            const bgColors = categoryChart.data.datasets[0].backgroundColor || colors;
            let html = '<ul class="list-unstyled mb-0">';
            for (let i = 0; i < labelsArr.length; i++) {
                const val = values[i] || 0;
                const pct = (val / total) * 100;
                const color = bgColors[i] || colors[i % colors.length];
                html += `<li class="mb-1"><span style="display:inline-block;width:12px;height:12px;background:${color};margin-right:8px;border-radius:2px;"></span><strong>${labelsArr[i]}</strong>: ₱${val.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})} — ${pct.toFixed(2)}%</li>`;
            }
            html += '</ul>';
            listEl.innerHTML = html;
        })();

    })();
</script>

<?php include 'layout/footer.php' ?>