<?php
require_once __DIR__ . '/../auth/session.php';
require_auth();

require __DIR__ . '/../database/Database.php';
require __DIR__ . '/../models/Transactions.php';
require __DIR__ . '/../models/Prediction.php';
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../layout/icons.php';
require __DIR__ . '/../layout/header.php';

$db = new Database();
$conn = $db->getConnection();

Transactions::setConnection($conn);
Prediction::setConnection($conn);

// Get prediction data
$dailyTrend = Prediction::getDailySpendingTrend(30);
$categoryAvg = Prediction::getAverageByCategoryLastDays(30);
$nextMonthPrediction = Prediction::predictNextMonthSpending();
$monthComparison = Prediction::getMonthComparison();
$budgetHealth = Prediction::getBudgetHealth();

// Format data for charts
$trendDates = [];
$trendExpenses = [];
$trendDeposits = [];

$points = [];
foreach ($dailyTrend as $point) {
    $points[$point['date']][$point['category']] = (float)$point['total'];
}
foreach ($points as $date => $values) {
    $trendDates[] = $date;
    $trendExpenses[] = $values['expense'] ?? 0;
    $trendDeposits[] = $values['deposit'] ?? 0;
}

// Prepare prediction data
$predictionCategories = [];
$predictionAmounts = [];
foreach ($nextMonthPrediction as $pred) {
    $predictionCategories[] = ucfirst($pred['category']);
    $predictionAmounts[] = (float)$pred['predicted_amount'];
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Analytics & Predictions</h1>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <!-- Budget Health Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="p-4 bg-white rounded-4 shadow-lg border border-light">
                <h3 class="mb-4 card-label"><?= card_icon('wallet') ?><span>This Month: Expenses Against Deposits</span></h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar <?php 
                                echo $budgetHealth['health_status'] === 'good' ? 'bg-success' : 
                                     ($budgetHealth['health_status'] === 'warning' ? 'bg-warning' : 'bg-danger');
                            ?>" role="progressbar" style="width: <?= $budgetHealth['percent_used'] ?>%" 
                                aria-valuenow="<?= $budgetHealth['percent_used'] ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= $budgetHealth['actual_percent'] === null ? 'N/A' : round($budgetHealth['actual_percent'], 1) . '%' ?>
                            </div>
                        </div>
                        <p class="mt-2"><small>of this month's deposits used (N/A without deposits)</small></p>
                    </div>
                    <div class="col-md-6">
                        <div class="row text-center">
                            <div class="col">
                                <h5>₱<?= number_format($budgetHealth['expenses'], 2) ?></h5>
                                <p><small>Total Expenses</small></p>
                            </div>
                            <div class="col">
                                <h5 class="<?= $budgetHealth['remaining'] < 0 ? 'text-danger' : 'text-success' ?>">₱<?= number_format($budgetHealth['remaining'], 2) ?></h5>
                                <p><small>Remaining</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="p-4 bg-white rounded-4 shadow-lg border border-light">
                <h3 class="mb-4 card-label"><?= card_icon('chart') ?><span>30-Day Spending Trend</span></h3>
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-4 bg-white rounded-4 shadow-lg border border-light">
                <h3 class="mb-4 card-label"><?= card_icon('net') ?><span>Next Month Estimate</span></h3><p class="text-muted small">Last 30 calendar days, including days without transactions, multiplied by next month's days. This is an estimate.</p>
                <canvas id="predictionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Analytics -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="p-4 bg-white rounded-4 shadow-lg border border-light">
                <h3 class="mb-4 card-label"><?= card_icon('report') ?><span>Category Performance (Last 30 Days)</span></h3>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Amount</th>
                            <th>Transactions</th>
                            <th>Daily Average</th>
                            <th>Predicted Monthly</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nextMonthPrediction as $pred): ?>
                            <?php 
                            $categoryData = array_filter($categoryAvg, fn($c) => strtolower($c['category']) === strtolower($pred['category']));
                            $catData = reset($categoryData);
                            ?>
                            <tr>
                                <td><strong><?= ucfirst($pred['category']) ?></strong></td>
                                <td>₱<?= number_format($catData['total'] ?? 0, 2) ?></td>
                                <td><?= $catData['count'] ?? 0 ?></td>
                                <td>₱<?= number_format($pred['daily_average'], 2) ?></td>
                                <td class="text-primary"><strong>₱<?= number_format($pred['predicted_amount'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = 'Segoe UI, Arial, sans-serif';
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($trendDates) ?>,
            datasets: [
                {
                    label: 'Expenses',
                    data: <?= json_encode($trendExpenses) ?>,
                    borderColor: '#e1645a',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Deposits',
                    data: <?= json_encode($trendDeposits) ?>,
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });

    // Prediction Chart
    const predCtx = document.getElementById('predictionChart').getContext('2d');
    new Chart(predCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($predictionCategories) ?>,
            datasets: [{
                label: 'Predicted Amount',
                data: <?= json_encode($predictionAmounts) ?>,
                backgroundColor: [
                    '#4f46e5',
                    '#0d9488',
                    '#ffc107',
                    '#e1645a',
                    '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
