<?php
/**
 * IRONCORE - Get Single Membership API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$membershipId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (!$membershipId) {
    json_response(false, 'Membership ID is required.');
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            ms.*,
            m.member_code,
            u.name AS member_name,
            u.email AS member_email,
            m.phone AS member_phone,
            p.name AS plan_name,
            p.duration AS plan_duration,
            p.price AS plan_price,
            p.features AS plan_features,
            CAST(julianday(ms.end_date) - julianday(date('now')) AS INTEGER) AS days_remaining
        FROM memberships ms
        JOIN members m ON ms.member_id = m.id
        JOIN users u ON m.user_id = u.id
        JOIN membership_plans p ON ms.plan_id = p.id
        WHERE ms.id = ? LIMIT 1
    ");
    $stmt->execute([$membershipId]);
    $membership = $stmt->fetch();

    if (!$membership) {
        json_response(false, 'Membership record not found.', null, 404);
    }

    // Get payments linked to this membership
    $payStmt = $pdo->prepare("SELECT * FROM payments WHERE membership_id = ? ORDER BY id DESC");
    $payStmt->execute([$membershipId]);
    $payments = $payStmt->fetchAll();

    json_response(true, 'Membership loaded successfully.', [
        'membership' => $membership,
        'payments'   => $payments
    ]);

} catch (Exception $e) {
    json_response(false, 'Error fetching membership: ' . $e->getMessage(), null, 500);
}
