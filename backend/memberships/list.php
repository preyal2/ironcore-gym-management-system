<?php
/**
 * IRONCORE - List Memberships API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$status   = trim($_GET['status'] ?? '');
$memberId = isset($_GET['member_id']) && !empty($_GET['member_id']) ? (int)$_GET['member_id'] : null;

// If member is viewing, force member's own ID
if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

try {
    $query = "
        SELECT 
            ms.id AS membership_id,
            ms.member_id,
            m.member_code,
            u.name AS member_name,
            u.email AS member_email,
            m.phone AS member_phone,
            p.id AS plan_id,
            p.name AS plan_name,
            p.duration,
            p.price,
            ms.start_date,
            ms.end_date,
            ms.status,
            CAST(julianday(ms.end_date) - julianday(date('now')) AS INTEGER) AS days_remaining,
            ms.created_at
        FROM memberships ms
        JOIN members m ON ms.member_id = m.id
        JOIN users u ON m.user_id = u.id
        JOIN membership_plans p ON ms.plan_id = p.id
        WHERE 1=1
    ";

    $params = [];

    if ($memberId) {
        $query .= " AND ms.member_id = ?";
        $params[] = $memberId;
    }

    if (!empty($status)) {
        if ($status === 'active') {
            $query .= " AND ms.status = 'active' AND ms.end_date >= CURRENT_DATE";
        } elseif ($status === 'expiring_soon') {
            $query .= " AND ms.status = 'active' AND CAST(julianday(ms.end_date) - julianday(date('now')) AS INTEGER) BETWEEN 0 AND 14";
        } elseif ($status === 'expired') {
            $query .= " AND (ms.status = 'expired' OR ms.end_date < CURRENT_DATE)";
        } else {
            $query .= " AND ms.status = ?";
            $params[] = $status;
        }
    }

    $query .= " ORDER BY ms.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $memberships = $stmt->fetchAll();

    json_response(true, 'Memberships retrieved.', [
        'count'       => count($memberships),
        'memberships' => $memberships
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch memberships: ' . $e->getMessage(), null, 500);
}
