<?php
/**
 * IRONCORE - Member Check-Out API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId = !empty($input['member_id']) ? (int)$input['member_id'] : null;

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

if (!$memberId) {
    json_response(false, 'Member ID is required.');
}

try {
    $today = date('Y-m-d');
    $currentTime = date('H:i:s');

    // Find today's checkin
    $stmt = $pdo->prepare("SELECT id, check_in_time, check_out_time FROM attendance WHERE member_id = ? AND attendance_date = ? LIMIT 1");
    $stmt->execute([$memberId, $today]);
    $attendance = $stmt->fetch();

    if (!$attendance) {
        json_response(false, 'No check-in record found for today. Please check in first.');
    }

    if (!empty($attendance['check_out_time'])) {
        json_response(true, 'Already checked out today.', [
            'check_out_time' => date('h:i A', strtotime($attendance['check_out_time']))
        ]);
    }

    // Update Checkout time
    $upStmt = $pdo->prepare("UPDATE attendance SET check_out_time = ? WHERE id = ?");
    $upStmt->execute([$currentTime, $attendance['id']]);

    json_response(true, 'Check-out successful. Great workout session!', [
        'check_out_time' => date('h:i A', strtotime($currentTime))
    ]);

} catch (Exception $e) {
    json_response(false, 'Check-out failed: ' . $e->getMessage(), null, 500);
}
