<?php
/**
 * IRONCORE - Add Member API (Admin / Staff)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$name      = trim($input['name'] ?? '');
$email     = trim($input['email'] ?? '');
$phone     = trim($input['phone'] ?? '');
$password  = trim($input['password'] ?? 'member123'); // Default initial password if not specified
$gender    = trim($input['gender'] ?? 'Male');
$dob       = !empty($input['date_of_birth']) ? $input['date_of_birth'] : null;
$address   = trim($input['address'] ?? '');
$height    = !empty($input['height']) ? (float)$input['height'] : null;
$weight    = !empty($input['weight']) ? (float)$input['weight'] : null;
$goal      = trim($input['fitness_goal'] ?? 'General Fitness');
$level     = trim($input['fitness_level'] ?? 'Beginner');
$trainerId = !empty($input['trainer_id']) ? (int)$input['trainer_id'] : null;
$planId    = !empty($input['plan_id']) ? (int)$input['plan_id'] : null;
$paymentMethod = trim($input['payment_method'] ?? 'UPI');

if (empty($name) || empty($email) || empty($phone)) {
    json_response(false, 'Full Name, Email, and Phone number are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Invalid email address.');
}

try {
    // Check email uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(false, 'An account with this email already exists.');
    }

    $pdo->beginTransaction();

    // 1. Create User
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $uStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'member', 'active')");
    $uStmt->execute([$name, $email, $hashed]);
    $userId = (int)$pdo->lastInsertId();

    // 2. Generate Member Code
    $memberCode = 'IC-' . str_pad((string)(1000 + $userId), 4, '0', STR_PAD_LEFT);

    // 3. Create Member Profile
    $mStmt = $pdo->prepare("
        INSERT INTO members (user_id, member_code, phone, gender, date_of_birth, address, height, weight, fitness_goal, fitness_level, trainer_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $mStmt->execute([$userId, $memberCode, $phone, $gender, $dob, $address, $height, $weight, $goal, $level, $trainerId]);
    $memberId = (int)$pdo->lastInsertId();

    // 4. Record Initial Weight in Progress Table
    if ($weight) {
        $pStmt = $pdo->prepare("INSERT INTO progress (member_id, weight, record_date, notes) VALUES (?, ?, CURRENT_DATE, 'Initial intake measurement')");
        $pStmt->execute([$memberId, $weight]);
    }

    // 5. Assign Membership Plan if selected
    if ($planId) {
        $plnStmt = $pdo->prepare("SELECT id, duration_days, price FROM membership_plans WHERE id = ?");
        $plnStmt->execute([$planId]);
        $plan = $plnStmt->fetch();

        if ($plan) {
            $days = (int)($plan['duration_days'] ?? 30);
            $startDate = date('Y-m-d');
            $endDate   = date('Y-m-d', strtotime("+{$days} days"));

            $mbStmt = $pdo->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
            $mbStmt->execute([$memberId, $plan['id'], $startDate, $endDate]);
            $membershipId = (int)$pdo->lastInsertId();

            // Record Payment
            $receiptNo = 'REC-' . date('Y') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $payStmt = $pdo->prepare("INSERT INTO payments (receipt_number, member_id, membership_id, amount, payment_method, payment_status, notes) VALUES (?, ?, ?, ?, ?, 'Completed', 'Initial membership payment')");
            $payStmt->execute([$receiptNo, $memberId, $membershipId, $plan['price'], $paymentMethod]);
        }
    }

    // 6. Notification
    $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Welcome to IronCore', 'Your membership profile has been created successfully.', 'general')");
    $nStmt->execute([$userId]);

    $pdo->commit();

    json_response(true, 'Member added successfully with Member ID ' . $memberCode, [
        'member_id'   => $memberId,
        'member_code' => $memberCode
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to add member: ' . $e->getMessage(), null, 500);
}
