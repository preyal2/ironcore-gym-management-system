<?php
/**
 * IRONCORE - Payment & Collection Reports API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

try {
    // 1. Total Revenue
    $totRevStmt = $pdo->query("SELECT SUM(amount) AS total FROM payments WHERE payment_status = 'Completed'");
    $totalRevenue = (float)($totRevStmt->fetch()['total'] ?? 0);

    $thirtyDaysAgo = sql_date_n_days_ago(30);
    $mRevStmt = $pdo->query("SELECT SUM(amount) AS this_month FROM payments WHERE payment_status = 'Completed' AND payment_date >= {$thirtyDaysAgo}");
    $thisMonthRevenue = (float)($mRevStmt->fetch()['this_month'] ?? 0);

    // 3. Pending Payments Sum
    $penStmt = $pdo->query("SELECT SUM(amount) AS pending_amount, COUNT(*) AS pending_count FROM payments WHERE payment_status = 'Pending'");
    $pendingData = $penStmt->fetch();

    // 4. Breakdown by Payment Method
    $methStmt = $pdo->query("
        SELECT payment_method, SUM(amount) AS total_amount, COUNT(*) AS count
        FROM payments
        WHERE payment_status = 'Completed'
        GROUP BY payment_method
    ");
    $methodBreakdown = $methStmt->fetchAll();

    // 5. Recent 50 Payments
    $recStmt = $pdo->query("
        SELECT p.*, m.member_code, u.name AS member_name, mp.name AS plan_name
        FROM payments p
        JOIN members m ON p.member_id = m.id
        JOIN users u ON m.user_id = u.id
        LEFT JOIN memberships ms ON p.membership_id = ms.id
        LEFT JOIN membership_plans mp ON ms.plan_id = mp.id
        ORDER BY p.payment_date DESC LIMIT 50
    ");
    $recentPayments = $recStmt->fetchAll();

    json_response(true, 'Payment reports generated.', [
        'total_revenue'      => $totalRevenue,
        'this_month_revenue' => $thisMonthRevenue,
        'pending_amount'     => (float)($pendingData['pending_amount'] ?? 0),
        'pending_count'      => (int)($pendingData['pending_count'] ?? 0),
        'method_breakdown'   => $methodBreakdown,
        'recent_payments'    => $recentPayments
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to generate payment report: ' . $e->getMessage(), null, 500);
}
