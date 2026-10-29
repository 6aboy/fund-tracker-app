<?php

/** Shared reporting rules for the dashboard and analytics. Amounts are stored unsigned. */
class FundSummary
{
    public static function build(array $transactions, int $days = 7, ?DateTimeImmutable $today = null): array
    {
        $today = ($today ?? new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->setTime(0, 0);
        $days = max(1, $days);
        $start = $today->modify('-' . ($days - 1) . ' days');
        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->modify("+$i days")->format('Y-m-d');
            $series[$date] = ['date' => $date, 'deposit' => 0.0, 'expense' => 0.0, 'balance' => 0.0];
        }
        $totals = $period = $daily = $monthly = ['deposit' => 0.0, 'expense' => 0.0];
        $counts = ['deposit' => 0, 'expense' => 0];
        $opening = 0.0;
        foreach ($transactions as $transaction) {
            $row = (array) $transaction;
            $category = strtolower(trim($row['category'] ?? ''));
            $date = substr($row['date_created'] ?? '', 0, 10);
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            $amount = filter_var($row['amount'] ?? null, FILTER_VALIDATE_FLOAT);
            if (!isset($totals[$category]) || !$parsed || $parsed->format('Y-m-d') !== $date || $date > $today->format('Y-m-d') || $amount === false || !is_finite($amount) || $amount < 0) {
                continue;
            }
            $totals[$category] += $amount;
            if (substr($date, 0, 7) === $today->format('Y-m')) $monthly[$category] += $amount;
            if ($date === $today->format('Y-m-d')) $daily[$category] += $amount;
            if (isset($series[$date])) {
                $series[$date][$category] += $amount;
                $period[$category] += $amount;
                $counts[$category]++;
            } else {
                $opening += $category === 'deposit' ? $amount : -$amount;
            }
        }
        $balance = $opening;
        foreach ($series as &$point) {
            $balance += $point['deposit'] - $point['expense'];
            $point['balance'] = round($balance, 2);
        }
        unset($point);
        return compact('totals', 'period', 'daily', 'monthly', 'counts', 'opening') + [
            'balance' => round($totals['deposit'] - $totals['expense'], 2),
            'series' => array_values($series), 'days' => $days,
        ];
    }
}
