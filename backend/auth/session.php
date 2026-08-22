<?php
/**
 * IRONCORE - Session Checker API
 * Returns current authenticated user details and active session status
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

start_session_safe();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    json_response(false, 'No active session found.', ['authenticated' => false], 401);
}

try {
    $pdo = get_db();
    $userId = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT id, name, email, role, status, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        session_destroy();
        json_response(false, 'Session invalid or user suspended.', ['authenticated' => false], 401);
    }

    $extra = [];

    // If Member
    if ($user['role'] === 'member') {
        $mStmt = $pdo->prepare("
            SELECT m.id AS member_id, m.member_code, m.phone, m.gender, m.height, m.weight,
                   m.fitness_goal, m.fitness_level, m.trainer_id, m.profile_image,
                   t.id AS assigned_trainer_id, tu.name AS trainer_name,
                   p.name AS current_plan_name, ms.end_date AS plan_expiry_date, ms.status AS plan_status
            FROM members m
            LEFT JOIN trainers t ON m.trainer_id = t.id
            LEFT JOIN users tu ON t.user_id = tu.id
            LEFT JOIN memberships ms ON ms.member_id = m.id AND ms.status = 'active'
            LEFT JOIN membership_plans p ON ms.plan_id = p.id
            WHERE m.user_id = ?
            ORDER BY ms.id DESC LIMIT 1
        ");
        $mStmt->execute([$userId]);
        $extra = $mStmt->fetch() ?: [];
        $_SESSION['member_id'] = $extra['member_id'] ?? null;
    }

    // If Trainer
    if ($user['role'] === 'trainer') {
        $tStmt = $pdo->prepare("
            SELECT t.id AS trainer_id, t.specialization, t.experience, t.phone, t.bio, t.profile_image,
                   (SELECT COUNT(*) FROM members WHERE trainer_id = t.id) AS assigned_members_count
            FROM trainers t
            WHERE t.user_id = ?
            LIMIT 1
        ");
        $tStmt->execute([$userId]);
        $extra = $tStmt->fetch() ?: [];
        $_SESSION['trainer_id'] = $extra['trainer_id'] ?? null;
    }

    // Unread Notifications Count
    $nStmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $nStmt->execute([$userId]);
    $notif = $nStmt->fetch();
    $unreadCount = (int)($notif['unread_count'] ?? 0);

    json_response(true, 'Active session verified.', [
        'authenticated' => true,
        'user' => [
            'id'           => (int)$user['id'],
            'name'         => $user['name'],
            'email'        => $user['email'],
            'role'         => $user['role'],
            'created_at'   => $user['created_at'],
            'unread_notif' => $unreadCount,
            'details'      => $extra
        ]
    ]);

} catch (Exception $e) {
    json_response(false, 'Session check error: ' . $e->getMessage(), ['authenticated' => false], 500);
}
