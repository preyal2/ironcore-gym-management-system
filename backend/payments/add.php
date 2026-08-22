<?php
/**
 * IRONCORE - Add Payment API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId      = !empty($input['member_id']) ? (int)$input['member_id'] : null;
$amount        = !empty($input['amount']) ? (float)$input['amount'] : null;
$paymentMethod = trim($input['payment_method'] ?? 'UPI');
$paymentStatus = trim($input['payment_status'] ?? 'Completed');
$txnRef        = trim($input['transaction_reference'] ?? '');
$notes         = trim($input['notes'] ?? 'Manual Payment Record');
$membershipId  = !empty($input['membership_id']) ? (int)$input['membership_id'] : null;

if (!$memberId || !$amount || $amount <= 0) {
    json_response(false, 'Valid Member ID and positive amount are required.');
}

try {
    // Verify member exists
    $mStmt = $pdo->prepare("SELECT user_id, member_code FROM members WHERE id = ?");
    $mStmt->execute([$memberId]);
    $member = $mStmt->fetch();
    if (!$member) {
        json_response(false, 'Member not found.');
    }

    $receiptNo = 'REC-' . date('Y') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    if (empty($txnRef)) {
        $txnRef = strtoupper($paymentMethod) . '/' . time() . rand(10, 99);
    }

    $stmt = $pdo->prepare("
        INSERT INTO payments (receipt_number, member_id, membership_id, amount, payment_method, payment_status, transaction_reference, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$receiptNo, $memberId, $membershipId, $amount, $paymentMethod, $paymentStatus, $txnRef, $notes]);
    $paymentId = (int)$pdo->lastInsertId();

    // Create Notification
    $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Payment Received', ?, 'payment')");
    $nStmt->execute([(int)$member['user_id'], "Payment of ₹" . number_format($amount, 2) . " received via {$paymentMethod}. Receipt: {$receiptNo}."]);

    json_response(true, 'Payment recorded successfully.', [
        'payment_id'     => $paymentId,
        'receipt_number' => $receiptNo,
        'amount'         => $amount
    ], 201);

} catch (Exception $e) {
    json_response(false, 'Failed to record payment: ' . $e->getMessage(), null, 500);
}
