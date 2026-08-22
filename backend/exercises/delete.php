<?php
/**
 * IRONCORE - Delete Exercise API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$exerciseId = !empty($input['id']) ? (int)$input['id'] : null;

if (!$exerciseId) {
    json_response(false, 'Exercise ID is required.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM exercises WHERE id = ?");
    $stmt->execute([$exerciseId]);

    json_response(true, 'Exercise deleted successfully.');

} catch (Exception $e) {
    json_response(false, 'Failed to delete exercise: ' . $e->getMessage(), null, 500);
}
