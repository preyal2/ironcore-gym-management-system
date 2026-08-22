<?php
/**
 * IRONCORE - Reject Appointment API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$appointmentId = !empty($input['id']) ? (int)$input['id'] : null;
$reason        = trim($input['reason'] ?? 'Trainer unavailable at this slot');

if (!$appointmentId) {
    json_response(false, 'Appointment ID is required.');
}

try {
    $stmt = $pdo->prepare("SELECT a.*, m.user_id AS member_user_id, tu.name AS trainer_name FROM appointments a JOIN members m ON a.member_id = m.id JOIN trainers t ON a.trainer_id = t.id JOIN users tu ON t.user_id = tu.id WHERE a.id = ?");
    $stmt->execute([$appointmentId]);
    $app = $stmt->fetch();

    if (!$app) {
        json_response(false, 'Appointment not found.', null, 404);
    }

    $pdo->beginTransaction();

    $upStmt = $pdo->prepare("UPDATE appointments SET status = 'Rejected', notes = ? WHERE id = ?");
    $upStmt->execute([$reason, $appointmentId]);

    // Send notification to member
    $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Appointment Declined', ?, 'appointment')");
    $nStmt->execute([(int)$app['member_user_id'], "Your session request with Coach {$app['trainer_name']} on " . date('d M Y', strtotime($app['appointment_date'])) . " was declined ({$reason}). Please choose another time slot."]);

    $pdo->commit();

    json_response(true, 'Appointment rejected.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to reject appointment: ' . $e->getMessage(), null, 500);
}
