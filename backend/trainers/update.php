<?php
/**
 * IRONCORE - Update Trainer API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$trainerId = !empty($input['trainer_id']) ? (int)$input['trainer_id'] : null;

// If trainer is updating their own profile
if ($auth['role'] === 'trainer') {
    $currentTrainerId = get_trainer_id_for_user($pdo, $auth['user_id']);
    $trainerId = $currentTrainerId;
}

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
    $userId = (int)$trainer['user_id'];

    $name           = trim($input['name'] ?? '');
    $phone          = trim($input['phone'] ?? '');
    $specialization = trim($input['specialization'] ?? '');
    $experience     = trim($input['experience'] ?? '');
    $bio            = trim($input['bio'] ?? '');
    $password       = trim($input['password'] ?? '');

    $pdo->beginTransaction();

    if (!empty($name)) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $uStmt = $pdo->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
            $uStmt->execute([$name, $hashed, $userId]);
        } else {
            $uStmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $uStmt->execute([$name, $userId]);
        }
    }

    $tStmt = $pdo->prepare("
        UPDATE trainers SET
            phone = COALESCE(NULLIF(?, ''), phone),
            specialization = COALESCE(NULLIF(?, ''), specialization),
            experience = COALESCE(NULLIF(?, ''), experience),
            bio = ?
        WHERE id = ?
    ");
    $tStmt->execute([$phone, $specialization, $experience, $bio, $trainerId]);

    $pdo->commit();

    json_response(true, 'Trainer profile updated successfully.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to update trainer: ' . $e->getMessage(), null, 500);
}
