<?php
/**
 * IRONCORE - Revenue Trends API
 * Returns monthly revenue trends for the last 6 months for Chart.js & Dashboard
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

try {
    // Generate past 6 months data points
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $key = date('Y-m', strtotime("-{$i} month"));
        $label = date('M Y', strtotime("-{$i} month"));
        $months[$key] = [
            'month_key'   => $key,
            'month_label' => $label,
            'total'       => 0.00,
            'count'       => 0
        ];
    }

    $monthFmt = sql_format_month('payment_date');
    $sixMonthsAgo = sql_date_n_months_ago(6);

    $stmt = $pdo->query("
        SELECT 
            {$monthFmt} AS month_key,
            SUM(amount) AS monthly_total,
            COUNT(*) AS tx_count
        FROM payments
        WHERE payment_status = 'Completed'
          AND payment_date >= {$sixMonthsAgo}
        GROUP BY {$monthFmt}
        ORDER BY {$monthFmt} ASC
    ");
    $dbData = $stmt->fetchAll();

    foreach ($dbData as $row) {
        $k = $row['month_key'];
        if (isset($months[$k])) {
            $months[$k]['total'] = (float)$row['monthly_total'];
            $months[$k]['count'] = (int)$row['tx_count'];
        }
    }

    // High level KPIs
    $totRevStmt = $pdo->query("SELECT SUM(amount) AS total FROM payments WHERE payment_status = 'Completed'");
    $grandTotal = (float)($totRevStmt->fetch()['total'] ?? 0);

    $totMembersStmt = $pdo->query("SELECT COUNT(*) AS total FROM members");
    $totalMembers = (int)($totMembersStmt->fetch()['total'] ?? 0);

    $activeMembersStmt = $pdo->query("SELECT COUNT(DISTINCT member_id) AS total FROM memberships WHERE status = 'active' AND end_date >= date('now')");
    $activeMembers = (int)($activeMembersStmt->fetch()['total'] ?? 0);

    $trainersStmt = $pdo->query("SELECT COUNT(*) AS total FROM trainers");
    $totalTrainers = (int)($trainersStmt->fetch()['total'] ?? 0);

    $today = date('Y-m-d');
    $todayAttStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM attendance WHERE attendance_date = ?");
    $todayAttStmt->execute([$today]);
    $todayAttendance = (int)($todayAttStmt->fetch()['total'] ?? 0);

    json_response(true, 'Revenue metrics loaded.', [
        'kpis' => [
            'total_revenue'    => $grandTotal,
            'total_members'    => $totalMembers,
            'active_members'   => $activeMembers,
            'expired_members'  => max(0, $totalMembers - $activeMembers),
            'total_trainers'   => $totalTrainers,
            'today_attendance' => $todayAttendance
        ],
        'monthly_revenue' => array_values($months)
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to calculate revenue: ' . $e->getMessage(), null, 500);
}
