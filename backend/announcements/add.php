<?php
/**
 * IRONCORE - Add Announcement API (Admin Only)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$title    = trim($input['title'] ?? '');
$content  = trim($input['content'] ?? '');
$role     = trim($input['target_role'] ?? 'all');
$priority = trim($input['priority'] ?? 'normal');

if (empty($title) || empty($content)) {
    json_response(false, 'Announcement Title and Content are required.');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO announcements (title, content, target_role, priority)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$title, $content, $role, $priority]);
    $annId = (int)$pdo->lastInsertId();

    json_response(true, 'Announcement posted successfully.', ['id' => $annId], 201);

} catch (Exception $e) {
    json_response(false, 'Failed to post announcement: ' . $e->getMessage(), null, 500);
}
