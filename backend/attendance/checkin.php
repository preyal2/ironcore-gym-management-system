<?php
/**
 * IRONCORE - Member Check-In API
 * Handles 1-click Check-in & simulated QR code scanner check-in
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId   = !empty($input['member_id']) ? (int)$input['member_id'] : null;
$memberCode = trim($input['member_code'] ?? '');

// If member is checking themselves in
if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
} elseif (!empty($memberCode) && !$memberId) {
    // Look up by member code (QR scan simulation)
    $stmt = $pdo->prepare("SELECT id FROM members WHERE member_code = ? LIMIT 1");
    $stmt->execute([$memberCode]);
    $found = $stmt->fetch();
    if ($found) {
        $memberId = (int)$found['id'];
    }
}

if (!$memberId) {
    json_response(false, 'Valid Member ID or Member Code is required for check-in.');
}

try {
    // 1. Verify Member Profile & Active Membership Status
    $mStmt = $pdo->prepare("
        SELECT m.id, m.member_code, u.name,
               ms.status AS membership_status, ms.end_date
        FROM members m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN memberships ms ON ms.member_id = m.id AND ms.status = 'active'
        WHERE m.id = ? LIMIT 1
    ");
    $mStmt->execute([$memberId]);
    $member = $mStmt->fetch();

    if (!$member) {
        json_response(false, 'Member not found.');
    }

    $today = date('Y-m-d');
    $currentTime = date('H:i:s');

    // 2. Check if already checked in today
    $chkStmt = $pdo->prepare("SELECT id, check_in_time, check_out_time FROM attendance WHERE member_id = ? AND attendance_date = ? LIMIT 1");
    $chkStmt->execute([$memberId, $today]);
    $existing = $chkStmt->fetch();

    if ($existing) {
        json_response(true, 'Already checked in today!', [
            'member_name'    => $member['name'],
            'member_code'    => $member['member_code'],
            'check_in_time'  => date('h:i A', strtotime($existing['check_in_time'])),
            'check_out_time' => $existing['check_out_time'] ? date('h:i A', strtotime($existing['check_out_time'])) : null,
            'already_in'     => true
        ]);
    }

    // 3. Record Check-In
    $insStmt = $pdo->prepare("
        INSERT INTO attendance (member_id, attendance_date, check_in_time, status)
        VALUES (?, ?, ?, 'Present')
    ");
    $insStmt->execute([$memberId, $today, $currentTime]);

    json_response(true, "Check-in successful! Welcome back, {$member['name']}.", [
        'member_name'   => $member['name'],
        'member_code'   => $member['member_code'],
        'check_in_time' => date('h:i A', strtotime($currentTime)),
        'already_in'    => false
    ], 201);

} catch (Exception $e) {
    json_response(false, 'Check-in failed: ' . $e->getMessage(), null, 500);
}
