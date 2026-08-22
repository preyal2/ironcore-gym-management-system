<?php
/**
 * IRONCORE - Create Notification API (Admin / System)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$userId  = !empty($input['user_id']) ? (int)$input['user_id'] : null;
$title   = trim($input['title'] ?? '');
$message = trim($input['message'] ?? '');
$type    = trim($input['type'] ?? 'general');
$role    = trim($input['target_role'] ?? ''); // all, member, trainer

if (empty($title) || empty($message)) {
    json_response(false, 'Notification title and message are required.');
}

try {
    if ($userId) {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type]);
    } elseif (!empty($role)) {
        // Broadcast to specific role or all
        $userQuery = "SELECT id FROM users WHERE status = 'active'";
        $params = [];
        if ($role !== 'all') {
            $userQuery .= " AND role = ?";
            $params[] = $role;
        }
        $uStmt = $pdo->prepare($userQuery);
        $uStmt->execute($params);
        $users = $uStmt->fetchAll();

        $ins = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        foreach ($users as $u) {
            $ins->execute([(int)$u['id'], $title, $message, $type]);
        }
    }

    json_response(true, 'Notification sent successfully.', null, 201);

} catch (Exception $e) {
    json_response(false, 'Failed to create notification: ' . $e->getMessage(), null, 500);
}
