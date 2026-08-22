<?php
/**
 * IRONCORE - Delete Workout Plan API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$planId = !empty($input['id']) ? (int)$input['id'] : null;

if (!$planId) {
    json_response(false, 'Workout Plan ID is required.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM workout_plans WHERE id = ?");
    $stmt->execute([$planId]);

    json_response(true, 'Workout plan deleted successfully.');

} catch (Exception $e) {
    json_response(false, 'Failed to delete workout plan: ' . $e->getMessage(), null, 500);
}
