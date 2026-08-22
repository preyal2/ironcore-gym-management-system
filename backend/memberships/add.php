<?php
/**
 * IRONCORE - Add / Assign Membership Plan API (Admin)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId      = !empty($input['member_id']) ? (int)$input['member_id'] : null;
$planId        = !empty($input['plan_id']) ? (int)$input['plan_id'] : null;
$startDate     = !empty($input['start_date']) ? $input['start_date'] : date('Y-m-d');
$paymentMethod = trim($input['payment_method'] ?? 'UPI');
$amountPaid    = isset($input['amount']) ? (float)$input['amount'] : null;

if (!$memberId || !$planId) {
    json_response(false, 'Member ID and Plan ID are required.');
}

try {
    $pStmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ?");
    $pStmt->execute([$planId]);
    $plan = $pStmt->fetch();
    if (!$plan) {
        json_response(false, 'Invalid membership plan selected.');
    }

    $days = (int)($plan['duration_days'] ?? 30);
    $endDate = date('Y-m-d', strtotime($startDate . " +{$days} days"));
    $finalAmount = $amountPaid !== null ? $amountPaid : (float)$plan['price'];

    $pdo->beginTransaction();

    // Mark previous active memberships for this member as expired/replaced
    $upStmt = $pdo->prepare("UPDATE memberships SET status = 'expired' WHERE member_id = ? AND status = 'active'");
    $upStmt->execute([$memberId]);

    // Create new membership
    $insStmt = $pdo->prepare("
        INSERT INTO memberships (member_id, plan_id, start_date, end_date, status)
        VALUES (?, ?, ?, ?, 'active')
    ");
    $insStmt->execute([$memberId, $planId, $startDate, $endDate]);
    $membershipId = (int)$pdo->lastInsertId();

    // Generate Payment
    $receiptNo = 'REC-' . date('Y') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    $payStmt = $pdo->prepare("
        INSERT INTO payments (receipt_number, member_id, membership_id, amount, payment_method, payment_status, notes)
        VALUES (?, ?, ?, ?, ?, 'Completed', 'Membership assigned by admin')
    ");
    $payStmt->execute([$receiptNo, $memberId, $membershipId, $finalAmount, $paymentMethod]);

    // Get user id for notification
    $uStmt = $pdo->prepare("SELECT user_id FROM members WHERE id = ?");
    $uStmt->execute([$memberId]);
    $mRow = $uStmt->fetch();
    if ($mRow) {
        $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Membership Plan Activated', ?, 'membership')");
        $nStmt->execute([(int)$mRow['user_id'], "Your {$plan['name']} membership is now active until " . date('d M Y', strtotime($endDate)) . "."]);
    }

    $pdo->commit();

    json_response(true, 'Membership assigned successfully.', [
        'membership_id' => $membershipId,
        'receipt_no'    => $receiptNo,
        'end_date'      => $endDate
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to assign membership: ' . $e->getMessage(), null, 500);
}
