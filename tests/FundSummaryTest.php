<?php
require_once __DIR__ . '/../models/FundSummary.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$rows = [
 ['category'=>'deposit','amount'=>1000,'date_created'=>'2026-10-01'],
 ['category'=>'expense','amount'=>100,'date_created'=>'2026-10-23 09:00:00'],
 ['category'=>'deposit','amount'=>200,'date_created'=>'2026-10-29'],
 ['category'=>'expense','amount'=>50.25,'date_created'=>'2026-10-29'],
 ['category'=>'other','amount'=>999,'date_created'=>'2026-10-29'],
 ['category'=>'expense','amount'=>999,'date_created'=>'2026-10-30'],
 ['category'=>'expense','amount'=>999,'date_created'=>'2026-02-30'],
];
$s = FundSummary::build($rows, 7, new DateTimeImmutable('2026-10-29'));
check(count($s['series']) === 7, 'Seven calendar days required');
check($s['opening'] === 1000.0, 'Historical opening balance');
check($s['balance'] === 1049.75, 'Balance excludes future and invalid transactions');
check($s['series'][1]['expense'] === 0.0, 'Missing dates must be zero filled');
check($s['series'][6]['balance'] === $s['balance'], 'Chart must reconcile with balance card');
check($s['period']['expense'] === 150.25, 'Weekly expenses');
check($s['daily']['expense'] === 50.25, 'Daily expenses');
check(FundSummary::build([], 30)['balance'] === 0.0, 'Empty history');
echo "Fund summary checks passed\n";
