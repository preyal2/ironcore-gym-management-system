<?php
/**
 * IRONCORE - Book / Create Appointment API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId  = !empty($input['member_id']) ? (int)$input['member_id'] : null;
$trainerId = !empty($input['trainer_id']) ? (int)$input['trainer_id'] : null;
$date      = trim($input['appointment_date'] ?? '');
$time      = trim($input['appointment_time'] ?? '');
$purpose   = trim($input['purpose'] ?? 'Personal Training Consultation');
$notes     = trim($input['notes'] ?? '');

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

if (!$memberId || !$trainerId || empty($date) || empty($time)) {
    json_response(false, 'Trainer, Date, and Time are required.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO appointments (member_id, trainer_id, appointment_date, appointment_time, purpose, notes, status)
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt->execute([$memberId, $trainerId, $date, $time, $purpose, $notes]);
    $appId = (int)$pdo->lastInsertId();

    // Send notification to trainer
    $tStmt = $pdo->prepare("SELECT user_id FROM trainers WHERE id = ?");
    $tStmt->execute([$trainerId]);
    $tUser = $tStmt->fetch();
    if ($tUser) {
        $mStmt = $pdo->prepare("SELECT name FROM users u JOIN members m ON m.user_id = u.id WHERE m.id = ?");
        $mStmt->execute([$memberId]);
        $mUser = $mStmt->fetch();
        $memberName = $mUser ? $mUser['name'] : 'A member';

        $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'New Appointment Request', ?, 'appointment')");
        $nStmt->execute([(int)$tUser['user_id'], "{$memberName} requested a session on " . date('d M Y', strtotime($date)) . " at " . date('h:i A', strtotime($time)) . "."]);
    }

    $pdo->commit();

    json_response(true, 'Appointment request submitted successfully. Awaiting trainer confirmation.', [
        'appointment_id' => $appId
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to book appointment: ' . $e->getMessage(), null, 500);
}
