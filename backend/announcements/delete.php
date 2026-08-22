<?php
/**
 * IRONCORE - Delete Announcement API (Admin Only)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$id = !empty($input['id']) ? (int)$input['id'] : null;

if (!$id) {
    json_response(false, 'Announcement ID is required.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);

    json_response(true, 'Announcement deleted successfully.');

} catch (Exception $e) {
    json_response(false, 'Failed to delete announcement: ' . $e->getMessage(), null, 500);
}
