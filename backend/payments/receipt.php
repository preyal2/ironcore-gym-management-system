<?php
/**
 * IRONCORE - Printable Payment Receipt API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$receiptNo = trim($_GET['receipt_number'] ?? '');
$paymentId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (empty($receiptNo) && empty($paymentId)) {
    json_response(false, 'Receipt Number or Payment ID is required.');
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
            m.phone AS member_phone,
            m.address AS member_address,
            u.name AS member_name,
            u.email AS member_email,
            COALESCE(mp.name, 'General Gym Services') AS item_description,
            mp.duration AS plan_duration,
            ms.start_date,
            ms.end_date
        FROM payments p
        JOIN members m ON p.member_id = m.id
        JOIN users u ON m.user_id = u.id
        LEFT JOIN memberships ms ON p.membership_id = ms.id
        LEFT JOIN membership_plans mp ON ms.plan_id = mp.id
        WHERE " . (!empty($paymentId) ? "p.id = ?" : "p.receipt_number = ?") . "
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([!empty($paymentId) ? $paymentId : $receiptNo]);
    $receipt = $stmt->fetch();

    if (!$receipt) {
        json_response(false, 'Receipt not found.', null, 404);
    }

    json_response(true, 'Receipt retrieved.', [
        'receipt' => $receipt,
        'gym'     => [
            'name'     => 'IRONCORE FITNESS CENTER',
            'tagline'  => 'Train Hard. Stay Strong. Track Progress.',
            'phone'    => '+91 98765 00000',
            'email'    => 'support@ironcore.com',
            'address'  => 'Level 4, Titanium Square, Gym Avenue, Ahmedabad, Gujarat',
            'gstin'    => '24AAACI9928P1Z8'
        ]
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to load receipt: ' . $e->getMessage(), null, 500);
}
