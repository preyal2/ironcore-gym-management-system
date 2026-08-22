<?php
/**
 * IRONCORE - Login API
 * Validates credentials, creates PHP session, returns role-based redirect URL
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

start_session_safe();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($email) || empty($password)) {
    json_response(false, 'Please provide both email and password.');
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(false, 'Invalid email or password.');
    }

    if ($user['status'] !== 'active') {
        json_response(false, 'Your account is currently inactive or suspended. Please contact gym administration.');
    }

    // Verify Password (supports bcrypt and fallback plain for testing if needed)
    $passwordValid = password_verify($password, $user['password']) || ($password === $user['password']);

    if (!$passwordValid) {
        json_response(false, 'Invalid email or password.');
    }

    // Determine redirect path and IDs
    $role = $user['role'];
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $role;

    $redirect = '/frontend/member/dashboard.html';
    $profileData = [];

    if ($role === 'admin') {
        $redirect = '/frontend/admin/dashboard.html';
    } elseif ($role === 'trainer') {
        $redirect = '/frontend/trainer/dashboard.html';
        $trainerId = get_trainer_id_for_user($pdo, (int)$user['id']);
        $_SESSION['trainer_id'] = $trainerId;
        $profileData['trainer_id'] = $trainerId;
    } elseif ($role === 'member') {
        $redirect = '/frontend/member/dashboard.html';
        $memberId = get_member_id_for_user($pdo, (int)$user['id']);
        $_SESSION['member_id'] = $memberId;
        $profileData['member_id'] = $memberId;
    }

    json_response(true, 'Login successful. Welcome back, ' . htmlspecialchars($user['name']) . '!', [
        'user' => [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $role,
            'extra' => $profileData
        ],
        'redirect' => $redirect
    ]);

} catch (Exception $e) {
    json_response(false, 'Server error during login: ' . $e->getMessage(), null, 500);
}
