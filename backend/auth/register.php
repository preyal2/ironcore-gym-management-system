<?php
/**
 * IRONCORE - Public Member Registration API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

start_session_safe();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$name     = trim($input['name'] ?? '');
$email    = trim($input['email'] ?? '');
$phone    = trim($input['phone'] ?? '');
$password = trim($input['password'] ?? '');
$gender   = trim($input['gender'] ?? 'Male');
$goal     = trim($input['fitness_goal'] ?? 'General Fitness');
$planId   = isset($input['plan_id']) && !empty($input['plan_id']) ? (int)$input['plan_id'] : null;

if (empty($name) || empty($email) || empty($phone) || empty($password)) {
    json_response(false, 'Please fill in all required fields (Name, Email, Phone, Password).');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Please provide a valid email address.');
}

if (strlen($password) < 6) {
    json_response(false, 'Password must be at least 6 characters in length.');
}

try {
    $pdo = get_db();

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(false, 'An account with this email already exists. Please log in.');
    }

    $pdo->beginTransaction();

    // 1. Create User
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'member', 'active')");
    $stmt->execute([$name, $email, $hashedPassword]);
    $userId = (int)$pdo->lastInsertId();

    // 2. Generate Member Code
    $memberCode = 'IC-' . str_pad((string)(1000 + $userId), 4, '0', STR_PAD_LEFT);

    // 3. Create Member Profile
    $stmt = $pdo->prepare("INSERT INTO members (user_id, member_code, phone, gender, fitness_goal, fitness_level) VALUES (?, ?, ?, ?, ?, 'Beginner')");
    $stmt->execute([$userId, $memberCode, $phone, $gender, $goal]);
    $memberId = (int)$pdo->lastInsertId();

    // 4. Assign Default Plan if selected or fallback to Basic Plan
    $targetPlanId = $planId ?: 1;
    $planStmt = $pdo->prepare("SELECT id, duration_days, price FROM membership_plans WHERE id = ?");
    $planStmt->execute([$targetPlanId]);
    $plan = $planStmt->fetch();

    if ($plan) {
        $days = (int)($plan['duration_days'] ?? 30);
        $startDate = date('Y-m-d');
        $endDate   = date('Y-m-d', strtotime("+{$days} days"));

        $mStmt = $pdo->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
        $mStmt->execute([$memberId, $plan['id'], $startDate, $endDate]);
        $membershipId = (int)$pdo->lastInsertId();

        // Create initial payment record
        $receiptNo = 'REC-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);
        $pStmt = $pdo->prepare("INSERT INTO payments (receipt_number, member_id, membership_id, amount, payment_method, payment_status, notes) VALUES (?, ?, ?, ?, 'UPI', 'Completed', 'Welcome initial membership payment')");
        $pStmt->execute([$receiptNo, $memberId, $membershipId, $plan['price']]);
    }

    // 5. Create Welcome Notification
    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, 'general', 0)");
    $notifStmt->execute([
        $userId,
        'Welcome to IronCore!',
        'Your membership has been activated. Start tracking your workouts, diet, and progress today!'
    ]);

    $pdo->commit();

    // Set Session
    $_SESSION['user_id']   = $userId;
    $_SESSION['member_id'] = $memberId;
    $_SESSION['name']      = $name;
    $_SESSION['email']     = $email;
    $_SESSION['role']      = 'member';

    json_response(true, 'Registration successful! Welcome to the IronCore Fitness Family.', [
        'redirect' => '/frontend/member/dashboard.html'
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Registration failed: ' . $e->getMessage(), null, 500);
}
