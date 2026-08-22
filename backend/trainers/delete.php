<?php
/**
 * IRONCORE - Delete Trainer API (Admin Only)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$trainerId = !empty($input['trainer_id']) ? (int)$input['trainer_id'] : null;

if (!$trainerId) {
    json_response(false, 'Trainer ID is required.');
}

try {
    $stmt = $pdo->prepare("SELECT user_id FROM trainers WHERE id = ?");
    $stmt->execute([$trainerId]);
    $trainer = $stmt->fetch();

    if (!$trainer) {
        json_response(false, 'Trainer not found.', null, 404);
    }

    $pdo->beginTransaction();

    // Reassign any assigned members to NULL
    $reassignStmt = $pdo->prepare("UPDATE members SET trainer_id = NULL WHERE trainer_id = ?");
    $reassignStmt->execute([$trainerId]);

    // Deleting from users cascades to trainers
    $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delStmt->execute([(int)$trainer['user_id']]);

    $pdo->commit();

    json_response(true, 'Trainer removed successfully.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to delete trainer: ' . $e->getMessage(), null, 500);
}
