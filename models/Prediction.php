<?php
require_once __DIR__ . '/FundSummary.php';

class Prediction
{
    private static $conn;
    public static function setConnection($conn) { self::$conn = $conn; }
    private static function summary($days = 30)
    {
        $rows = self::$conn->query('SELECT category, amount, date_created FROM transaction')->fetchAll(PDO::FETCH_ASSOC);
        return FundSummary::build($rows, $days);
    }
    public static function getDailySpendingTrend($days = 30)
    {
        $result = [];
        foreach (self::summary($days)['series'] as $point) {
            foreach (['expense', 'deposit'] as $category) {
                $result[] = ['date' => $point['date'], 'category' => $category, 'total' => $point[$category]];
            }
        }
        return $result;
    }
    public static function getAverageByCategoryLastDays($days = 30)
    {
        $summary = self::summary($days);
        $result = [];
        foreach (['expense', 'deposit'] as $category) {
            $result[] = ['category' => $category, 'average' => $summary['period'][$category] / $summary['days'],
                'count' => $summary['counts'][$category], 'total' => $summary['period'][$category]];
        }
        return $result;
    }
    public static function predictNextMonthSpending()
    {
        $daysNextMonth = (int)(new DateTimeImmutable('first day of next month', new DateTimeZone('Asia/Manila')))->format('t');
        return array_map(fn($row) => ['category' => $row['category'],
            'predicted_amount' => round($row['average'] * $daysNextMonth, 2),
            'daily_average' => round($row['average'], 2)], self::getAverageByCategoryLastDays());
    }
    public static function getMonthComparison()
    {
        $today = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
        $start = $today->modify('first day of last month')->format('Y-m-d');
        $end = $today->modify('+1 day')->format('Y-m-d');
        $stmt = self::$conn->prepare("SELECT DATE_FORMAT(date_created, '%Y-%m') AS month, category, SUM(amount) AS total FROM transaction WHERE date_created >= ? AND date_created < ? AND category IN ('expense', 'deposit') GROUP BY month, category");
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getBudgetHealth($monthlyExpenseLimit = null)
    {
        $monthly = self::summary()['monthly'];
        $budget = $monthlyExpenseLimit === null ? $monthly['deposit'] : max(0, (float)$monthlyExpenseLimit);
        $percent = $budget > 0 ? $monthly['expense'] / $budget * 100 : null;
        return ['expenses' => $monthly['expense'], 'deposits' => $monthly['deposit'], 'budget' => $budget,
            'remaining' => $budget - $monthly['expense'], 'actual_percent' => $percent,
            'percent_used' => $percent === null ? ($monthly['expense'] > 0 ? 100 : 0) : min(100, $percent),
            'health_status' => ($percent === null ? $monthly['expense'] > 0 : $percent >= 80) ? 'critical' : ($percent >= 50 ? 'warning' : 'good')];
    }
}
