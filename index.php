<?php
require_once __DIR__ . '/auth/session.php';
require_auth();

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/models/Transactions.php';
require_once __DIR__ . '/models/FundSummary.php';
date_default_timezone_set('Asia/Manila');
$db = new Database();
Transactions::setConnection($db->getConnection());
$transactions = Transactions::all() ?? [];
usort($transactions, fn($a, $b) => strcmp($b->date_created ?? '', $a->date_created ?? '') ?: ((int)$b->id <=> (int)$a->id));
$summary = FundSummary::build($transactions);
$total_deposits = $summary['totals']['deposit'];
$total_expenses = $summary['totals']['expense'];
$total_budget = $summary['balance'];
$daily_deposit = $summary['daily']['deposit'];
$daily_expense = $summary['daily']['expense'];
$daily_net = $daily_deposit - $daily_expense;
$daily_percent = $daily_deposit > 0 ? $daily_net / $daily_deposit * 100 : null;
$weekly_deposits = $summary['period']['deposit'];
$weekly_expenses = $summary['period']['expense'];
$week_start = $summary['series'][0]['date'];
function format_currency($n) { return "\u{20B1}" . number_format((float)$n, 2); }
$chart_day_labels = json_encode(array_map(fn($p) => date('M j', strtotime($p['date'])), $summary['series']));
$chart_deposit_data = json_encode(array_column($summary['series'], 'deposit'));
$chart_expense_data = json_encode(array_column($summary['series'], 'expense'));
$chart_budget_data = json_encode(array_column($summary['series'], 'balance'));
$chart_cat_labels = json_encode(['Deposits', 'Expenses']);
$chart_cat_values = json_encode([$total_deposits, $total_expenses]);
require_once __DIR__ . '/layout/icons.php';
include __DIR__ . '/layout/header.php';
?>

<div class="container mt-0">
    <div class="page-heading d-flex justify-content-between align-items-center">
        <div><p class="eyebrow">YOUR MONEY, AT A GLANCE</p><h1>Overview</h1><p class="text-muted mb-0">A clearer picture of what comes in and what goes out.</p></div><a href="views/create.php" class="btn btn-primary">Add transaction</a>
    </div>

    <?php if (array_filter($transactions, fn($row) => str_starts_with($row->name ?? '', '[DEMO] '))): ?>
    <p class="demo-notice"><span class="demo-tag">SAMPLE DATA</span> Transactions marked [DEMO] are included in the figures below.</p>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-light rounded metric-card">
                <strong class="card-label"><?= card_icon('wallet', 'brand') ?><span>Available Balance</span></strong>
                <div class="h4 mt-1 text-primary"><?= htmlspecialchars(format_currency($total_budget)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded metric-card">
                <strong class="card-label"><?= card_icon('deposit', 'income') ?><span>Total Deposits</span></strong>
                <div class="h5 mt-2 text-success"><?= htmlspecialchars(format_currency($total_deposits)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded metric-card">
                <strong class="card-label"><?= card_icon('expense', 'expense') ?><span>Total Expenses</span></strong>
                <div class="h5 mt-2 text-danger"><?= htmlspecialchars(format_currency($total_expenses)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded metric-card">
                <strong class="card-label"><?= card_icon('calendar', 'brand') ?><span>Today's Date</span></strong>
                <div class="h5 mt-2"><?= htmlspecialchars(date('F j, Y')) ?></div>
            </div>
        </div>
    </div>

    <!-- Daily summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong class="card-label"><?= card_icon('deposit', 'income') ?><span>Daily Deposit</span></strong>
                <div class="h5 mt-2 text-success"><?= htmlspecialchars(format_currency($daily_deposit)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong class="card-label"><?= card_icon('expense', 'expense') ?><span>Daily Expense</span></strong>
                <div class="h5 mt-2 text-danger"><?= htmlspecialchars(format_currency($daily_expense)) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong class="card-label"><?= card_icon('net', 'brand') ?><span>Net Today</span></strong>
                <div class="h5 mt-2 <?= $daily_net >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= htmlspecialchars(format_currency($daily_net)) ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong class="card-label"><?= card_icon('percent', 'brand') ?><span>Net vs Deposit</span></strong>
                <?php $percent_label = $daily_percent === null ? 'N/A' : (($daily_percent >= 0 ? '+' : '') . number_format($daily_percent, 2) . '%'); ?>
                <div class="h5 mt-2 <?= $daily_percent >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= htmlspecialchars($percent_label) ?>
                </div>
                <small class="text-muted">of today's deposits; N/A when no deposits</small>
            </div>
        </div>
    </div>

    <!-- Weekly totals -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-light rounded metric-card">
                <strong class="card-label"><?= card_icon('report', 'brand') ?><span>Weekly Funds Report</span></strong>
                <div class="mt-2">Deposit: <span class="h5 mt-1 text-success"><?= htmlspecialchars(format_currency($weekly_deposits)) ?></span></div>
                <div class="mt-1">Expense: <span class="h5 mt-1 text-danger"><?= htmlspecialchars(format_currency($weekly_expenses)) ?></span></div>
                <small class="text-muted d-block mt-2">Since <?= htmlspecialchars(date('M j', strtotime($week_start))) ?></small>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong class="card-label"><?= card_icon('chart') ?><span>Cash flow &middot; Last 7 days</span></strong>
                <canvas id="dailyChart" height="150"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded shadow-sm border">
                <strong class="card-label"><?= card_icon('pie', 'income') ?><span>All-time Deposits vs Expenses</span></strong>
                <canvas id="categoryChart" height="150"></canvas>
                <div id="categoryPercentList" class="mt-2 small"></div>
            </div>
        </div>
    </div>

    <!-- Transactions table -->
    <div class="p-4 bg-white rounded-4 shadow-lg border border-light" style="overflow-x:auto;">
        <div class="d-flex justify-content-between align-items-center mb-3 gap-3"><h2 class="h5 mb-0">Transaction history</h2></div>
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
                        $sign = (strtolower($row->category ?? '') === 'expense') ? '-' : '';
                        $amountFormatted = number_format((float) ($row->amount ?? 0), 2);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$row->id) ?></td>
                            <td><?= htmlspecialchars($row->name) ?></td>
                            <td><span class="type-badge <?= strtolower(trim($row->category ?? '')) === 'deposit' ? 'type-deposit' : 'type-expense' ?>"><?= htmlspecialchars(ucfirst($row->category)) ?></span></td>
                            <td data-order="<?= (float)$row->amount * ($sign === '-' ? -1 : 1) ?>"><?= htmlspecialchars($sign . '₱' . $amountFormatted) ?></td>
                            <td data-order="<?= htmlspecialchars($row->date_created ?? '') ?>"><?= htmlspecialchars(@date('F j, Y', strtotime($row->date_created ?? ''))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js and initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = 'Segoe UI, Arial, sans-serif';
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
        }
        const labels = <?= $chart_day_labels ?? '[]' ?>;
        const depositData = <?= $chart_deposit_data ?? '[]' ?>;
        const expenseData = <?= $chart_expense_data ?? '[]' ?>;
        const catLabels = <?= $chart_cat_labels ?? '[]' ?>;
        const catValues = <?= $chart_cat_values ?? '[]' ?>;
        const budgetData = <?= $chart_budget_data ?? '[]' ?>;


        const toNums = arr => (Array.isArray(arr) ? arr.map(x => {
            const n = Number(x);
            return isNaN(n) ? 0 : n;
        }) : []);

        const depositNums = toNums(depositData);
        const expenseNums = toNums(expenseData);
        const budgetNums = toNums(budgetData);
        const catNums = toNums(catValues);


        const sum = arr => Array.isArray(arr) ? arr.reduce((s, v) => s + (isNaN(v) ? 0 : v), 0) : 0;

        // LINE CHART
        const dailyCanvas = document.getElementById('dailyChart');
        if (dailyCanvas && typeof Chart !== 'undefined') {
            const ctx = dailyCanvas.getContext('2d');
            if (dailyCanvas._chartInstance) dailyCanvas._chartInstance.destroy();

            const allValues = [...depositNums, ...expenseNums, ...budgetNums];
            const rawMin = allValues.length ? Math.min(...allValues) : 0;
            const rawMax = allValues.length ? Math.max(...allValues) : 0;
            const padding = (rawMax - rawMin) * 0.12 || Math.max(Math.abs(rawMax), 1) * 0.08;
            const suggestedMin = Math.floor((rawMin - padding) * 100) / 100;
            const suggestedMax = Math.ceil((rawMax + padding) * 100) / 100;


            const lineConfig = {
                type: 'line',
                data: {
                    labels: Array.isArray(labels) ? labels : [],
                    datasets: [
                        {
                            label: 'Deposits',
                            data: depositNums,
                            borderColor: 'rgba(13,148,136,0.95)',
                            backgroundColor: 'rgba(13,148,136,0.12)',
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            spanGaps: true
                        },
                        {
                            label: 'Expenses',
                            data: expenseNums,
                            borderColor: 'rgba(225,100,90,0.95)',
                            backgroundColor: 'rgba(225,100,90,0.12)',
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            spanGaps: true
                        },
                        {
                            label: 'Available Balance',
                            data: budgetNums,
                            borderColor: 'rgba(79,70,229,0.95)',
                            backgroundColor: 'rgba(79,70,229,0.12)',
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 3,
                            borderDash: [6, 4],
                            spanGaps: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    stacked: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMin: suggestedMin,
                            suggestedMax: suggestedMax
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.dataset.label ?? '';
                                    const raw = context.parsed && typeof context.parsed === 'object' ? context.parsed.y : context.raw;
                                    const val = (typeof raw === 'number') ? raw : Number(raw || 0);
                                    return label + ': ₱' + val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                            }
                        }
                    }
                }
            };

            dailyCanvas._chartInstance = new Chart(ctx, lineConfig);
        } else {
            console.warn('dailyChart canvas or Chart is not available');
        }

        // PIE CHART
        const catCanvas = document.getElementById('categoryChart');
        if (catCanvas && typeof Chart !== 'undefined') {
            const ctx2 = catCanvas.getContext('2d');
            if (catCanvas._chartInstance) catCanvas._chartInstance.destroy();

            const safeLabels = Array.isArray(catLabels) ? catLabels : [];
            const safeData = catNums;

            const pieConfig = {
                type: 'doughnut',
                data: {
                    labels: safeLabels,
                    datasets: [{
                        data: safeData,
                        backgroundColor: ['#0d9488', '#e1645a', '#007bff', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true, position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = Number(context.raw || 0);
                                    const total = sum(safeData);
                                    const pct = total ? (value / total * 100).toFixed(2) : '0.00';
                                    return label + ': ₱' + value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            };

            catCanvas._chartInstance = new Chart(ctx2, pieConfig);

            (function renderCategoryPercentList() {
                let listEl = document.getElementById('categoryPercentList');
                if (!listEl) {
                    listEl = document.createElement('div');
                    listEl.id = 'categoryPercentList';
                    listEl.className = 'mt-2 small';
                    catCanvas.parentNode.appendChild(listEl);
                }

                const values = Array.isArray(safeData) ? safeData.map(Number) : [];
                const total = sum(values);
                if (total === 0) {
                    listEl.innerHTML = '<div class="text-muted">No category data</div>';
                    return;
                }

                const labelsArr = Array.isArray(safeLabels) ? safeLabels : [];
                const bgColors = (catCanvas._chartInstance && catCanvas._chartInstance.data.datasets[0].backgroundColor) || ['#0d9488', '#e1645a'];
                let html = '<ul class="list-unstyled mb-0">';
                for (let i = 0; i < labelsArr.length; i++) {
                    const val = values[i] || 0;
                    const pct = total ? (val / total) * 100 : 0;
                    const color = bgColors[i] || bgColors[i % bgColors.length];
                    html += `<li class="mb-1"><span style="display:inline-block;width:12px;height:12px;background:${color};margin-right:8px;border-radius:2px;"></span><strong>${labelsArr[i]}</strong>: ₱${val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} — ${pct.toFixed(2)}%</li>`;
                }
                html += '</ul>';
                listEl.innerHTML = html;
            })();
        } else {
            console.warn('categoryChart canvas or Chart is not available');
        }

    } catch (err) {
        console.error('Error initializing charts:', err);
    }
});
</script>

<?php include 'layout/footer.php' ?>