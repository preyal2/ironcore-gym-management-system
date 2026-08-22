<?php
/**
 * IRONCORE - List Payments API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$search    = trim($_GET['search'] ?? '');
$method    = trim($_GET['method'] ?? '');
$status    = trim($_GET['status'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate   = trim($_GET['end_date'] ?? '');
$memberId  = isset($_GET['member_id']) && !empty($_GET['member_id']) ? (int)$_GET['member_id'] : null;

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

try {
    $query = "
        SELECT 
            p.id AS payment_id,
            p.receipt_number,
            p.amount,
            p.payment_method,
            p.payment_status,
            p.transaction_reference,
            p.notes,
            p.payment_date,
            m.id AS member_id,
            m.member_code,
            u.name AS member_name,
            u.email AS member_email,
            m.phone AS member_phone,
            mp.name AS plan_name
        FROM payments p
        JOIN members m ON p.member_id = m.id
        JOIN users u ON m.user_id = u.id
        LEFT JOIN memberships ms ON p.membership_id = ms.id
        LEFT JOIN membership_plans mp ON ms.plan_id = mp.id
        WHERE 1=1
    ";

    $params = [];

    if ($memberId) {
        $query .= " AND p.member_id = ?";
        $params[] = $memberId;
    }

    if (!empty($search)) {
        $query .= " AND (p.receipt_number LIKE ? OR u.name LIKE ? OR m.member_code LIKE ? OR p.transaction_reference LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($method)) {
        $query .= " AND p.payment_method = ?";
        $params[] = $method;
    }

    if (!empty($status)) {
        $query .= " AND p.payment_status = ?";
        $params[] = $status;
    }

    if (!empty($startDate)) {
        $query .= " AND DATE(p.payment_date) >= ?";
        $params[] = $startDate;
    }

    if (!empty($endDate)) {
        $query .= " AND DATE(p.payment_date) <= ?";
        $params[] = $endDate;
    }

    $query .= " ORDER BY p.payment_date DESC, p.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();

    // Summary calculations
    $totalRevenue = 0.00;
    $completedCount = 0;
    $pendingCount = 0;

    foreach ($payments as $pay) {
        if ($pay['payment_status'] === 'Completed') {
            $totalRevenue += (float)$pay['amount'];
            $completedCount++;
        } elseif ($pay['payment_status'] === 'Pending') {
            $pendingCount++;
        }
    }

    json_response(true, 'Payments retrieved successfully.', [
        'count'          => count($payments),
        'total_revenue'  => $totalRevenue,
        'completed_count'=> $completedCount,
        'pending_count'  => $pendingCount,
        'payments'       => $payments
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch payments: ' . $e->getMessage(), null, 500);
}
