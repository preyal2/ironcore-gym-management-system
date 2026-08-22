<?php
/**
 * IRONCORE - Get Payment Details API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$paymentId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (!$paymentId) {
    json_response(false, 'Payment ID is required.');
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            m.member_code,
            m.phone AS member_phone,
            u.name AS member_name,
            u.email AS member_email,
            mp.name AS plan_name,
            mp.duration AS plan_duration
        FROM payments p
        JOIN members m ON p.member_id = m.id
        JOIN users u ON m.user_id = u.id
        LEFT JOIN memberships ms ON p.membership_id = ms.id
        LEFT JOIN membership_plans mp ON ms.plan_id = mp.id
        WHERE p.id = ? LIMIT 1
    ");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch();

    if (!$payment) {
        json_response(false, 'Payment not found.', null, 404);
    }

    json_response(true, 'Payment details loaded.', ['payment' => $payment]);

} catch (Exception $e) {
    json_response(false, 'Error fetching payment: ' . $e->getMessage(), null, 500);
}
