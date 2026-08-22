<?php
/**
 * IRONCORE - List Notifications API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();
$userId = (int)$auth['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();

    $unreadCount = 0;
    foreach ($notifications as $n) {
        if (!$n['is_read']) {
            $unreadCount++;
        }
    }

    json_response(true, 'Notifications loaded.', [
        'unread_count'  => $unreadCount,
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch notifications: ' . $e->getMessage(), null, 500);
}
