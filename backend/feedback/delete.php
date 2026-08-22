<?php
/**
 * IRONCORE - Delete Feedback API (Admin Only)
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
    json_response(false, 'Feedback ID is required.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$id]);

    json_response(true, 'Feedback entry removed.');

} catch (Exception $e) {
    json_response(false, 'Failed to delete feedback: ' . $e->getMessage(), null, 500);
}
