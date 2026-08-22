<?php
/**
 * IRONCORE - Renew Membership API
 * Renews an existing or expired membership, extends date, records payment
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId      = !empty($input['member_id']) ? (int)$input['member_id'] : null;
$planId        = !empty($input['plan_id']) ? (int)$input['plan_id'] : null;
$paymentMethod = trim($input['payment_method'] ?? 'UPI');

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

if (!$memberId || !$planId) {
    json_response(false, 'Member ID and Plan ID are required for renewal.');
}

try {
    // 1. Fetch Plan details
    $plnStmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ?");
    $plnStmt->execute([$planId]);
    $plan = $plnStmt->fetch();
    if (!$plan) {
        json_response(false, 'Membership plan not found.');
    }

    $daysToAdd = (int)($plan['duration_days'] ?? 30);

    // 2. Fetch Latest Membership of Member
    $mStmt = $pdo->prepare("SELECT * FROM memberships WHERE member_id = ? ORDER BY id DESC LIMIT 1");
    $mStmt->execute([$memberId]);
    $lastMembership = $mStmt->fetch();

    $today = date('Y-m-d');
    $newStartDate = $today;

    if ($lastMembership && $lastMembership['end_date'] >= $today && $lastMembership['status'] === 'active') {
        // Extend from the current future end date
        $newStartDate = $lastMembership['end_date'];
    }

    $newEndDate = date('Y-m-d', strtotime($newStartDate . " +{$daysToAdd} days"));

    $pdo->beginTransaction();

    // Deactivate previous active memberships
    $deactStmt = $pdo->prepare("UPDATE memberships SET status = 'expired' WHERE member_id = ? AND status = 'active'");
    $deactStmt->execute([$memberId]);

    // Insert Renewed Membership
    $insStmt = $pdo->prepare("
        INSERT INTO memberships (member_id, plan_id, start_date, end_date, status)
        VALUES (?, ?, ?, ?, 'active')
    ");
    $insStmt->execute([$memberId, $planId, $newStartDate, $newEndDate]);
    $membershipId = (int)$pdo->lastInsertId();

    // Create Payment Receipt
    $receiptNo = 'REC-' . date('Y') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    $payStmt = $pdo->prepare("
        INSERT INTO payments (receipt_number, member_id, membership_id, amount, payment_method, payment_status, notes)
        VALUES (?, ?, ?, ?, ?, 'Completed', 'Membership Renewal')
    ");
    $payStmt->execute([$receiptNo, $memberId, $membershipId, $plan['price'], $paymentMethod]);

    // Send Notification to Member User
    $uStmt = $pdo->prepare("SELECT user_id FROM members WHERE id = ?");
    $uStmt->execute([$memberId]);
    $mRow = $uStmt->fetch();
    if ($mRow) {
        $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Membership Renewed', ?, 'membership')");
        $nStmt->execute([(int)$mRow['user_id'], "Your {$plan['name']} membership is renewed and valid until " . date('d M Y', strtotime($newEndDate)) . "."]);
    }

    $pdo->commit();

    json_response(true, 'Membership renewed successfully!', [
        'membership_id' => $membershipId,
        'plan_name'     => $plan['name'],
        'receipt_number'=> $receiptNo,
        'amount_paid'   => $plan['price'],
        'end_date'      => $newEndDate
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Renewal failed: ' . $e->getMessage(), null, 500);
}
