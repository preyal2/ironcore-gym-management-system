<?php
/**
 * IRONCORE - Add Trainer API (Admin Only)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$name           = trim($input['name'] ?? '');
$email          = trim($input['email'] ?? '');
$phone          = trim($input['phone'] ?? '');
$password       = trim($input['password'] ?? 'trainer123');
$specialization = trim($input['specialization'] ?? 'Fitness & Conditioning');
$experience     = trim($input['experience'] ?? '3 Years');
$bio            = trim($input['bio'] ?? '');

if (empty($name) || empty($email) || empty($phone)) {
    json_response(false, 'Trainer Name, Email, and Phone are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Invalid email address.');
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(false, 'A user with this email address already exists.');
    }

    $pdo->beginTransaction();

    // Create user
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $uStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'trainer', 'active')");
    $uStmt->execute([$name, $email, $hashed]);
    $userId = (int)$pdo->lastInsertId();

    // Create trainer profile
    $tStmt = $pdo->prepare("INSERT INTO trainers (user_id, specialization, experience, phone, bio) VALUES (?, ?, ?, ?, ?)");
    $tStmt->execute([$userId, $specialization, $experience, $phone, $bio]);
    $trainerId = (int)$pdo->lastInsertId();

    $pdo->commit();

    json_response(true, 'Trainer added successfully.', [
        'trainer_id' => $trainerId,
        'user_id'    => $userId
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to add trainer: ' . $e->getMessage(), null, 500);
}
