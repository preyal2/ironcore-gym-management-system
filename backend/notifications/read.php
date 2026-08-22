<?php
/**
 * IRONCORE - Mark Notification(s) as Read API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();
$userId = (int)$auth['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$notifId = !empty($input['id']) ? (int)$input['id'] : null;
$markAll = isset($input['all']) && $input['all'] === true;

try {
    if ($markAll) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
        json_response(true, 'All notifications marked as read.');
    } elseif ($notifId) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notifId, $userId]);
        json_response(true, 'Notification marked as read.');
    } else {
        json_response(false, 'Notification ID or "all" flag is required.');
    }
} catch (Exception $e) {
    json_response(false, 'Failed to update notification: ' . $e->getMessage(), null, 500);
}
