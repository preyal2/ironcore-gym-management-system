<?php
/**
 * IRONCORE - Update Member API
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

// If member is editing their own profile
if ($auth['role'] === 'member') {
    $currentMemberId = get_member_id_for_user($pdo, $auth['user_id']);
    $memberId = $currentMemberId;
}

if (!$memberId) {
    json_response(false, 'Member ID is required.');
}

try {
    // Get member user ID
    $stmt = $pdo->prepare("SELECT user_id FROM members WHERE id = ?");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();
    if (!$member) {
        json_response(false, 'Member not found.', null, 404);
    }
    $userId = (int)$member['user_id'];

    $name          = trim($input['name'] ?? '');
    $phone         = trim($input['phone'] ?? '');
    $gender        = trim($input['gender'] ?? '');
    $dob           = !empty($input['date_of_birth']) ? $input['date_of_birth'] : null;
    $address       = trim($input['address'] ?? '');
    $height        = !empty($input['height']) ? (float)$input['height'] : null;
    $weight        = !empty($input['weight']) ? (float)$input['weight'] : null;
    $goal          = trim($input['fitness_goal'] ?? '');
    $level         = trim($input['fitness_level'] ?? '');
    $trainerId     = isset($input['trainer_id']) && $input['trainer_id'] !== '' ? (int)$input['trainer_id'] : null;
    $password      = trim($input['password'] ?? '');

    $pdo->beginTransaction();

    // Update User Name / Password if provided
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

    // Update Member details
    $mStmt = $pdo->prepare("
        UPDATE members SET
            phone = COALESCE(NULLIF(?, ''), phone),
            gender = COALESCE(NULLIF(?, ''), gender),
            date_of_birth = ?,
            address = ?,
            height = ?,
            weight = ?,
            fitness_goal = COALESCE(NULLIF(?, ''), fitness_goal),
            fitness_level = COALESCE(NULLIF(?, ''), fitness_level),
            trainer_id = ?
        WHERE id = ?
    ");
    $mStmt->execute([$phone, $gender, $dob, $address, $height, $weight, $goal, $level, $trainerId, $memberId]);

    // If new weight logged, save to progress
    if ($weight) {
        $pStmt = $pdo->prepare("INSERT INTO progress (member_id, weight, record_date, notes) VALUES (?, ?, CURRENT_DATE, 'Updated via profile')");
        $pStmt->execute([$memberId, $weight]);
    }

    $pdo->commit();

    json_response(true, 'Member profile updated successfully.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to update member: ' . $e->getMessage(), null, 500);
}
