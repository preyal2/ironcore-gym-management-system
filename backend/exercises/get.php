<?php
/**
 * IRONCORE - Get Single Exercise API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();
$exerciseId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (!$exerciseId) {
    json_response(false, 'Exercise ID is required.');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE id = ? LIMIT 1");
    $stmt->execute([$exerciseId]);
    $exercise = $stmt->fetch();

    if (!$exercise) {
        json_response(false, 'Exercise not found.', null, 404);
    }

    json_response(true, 'Exercise loaded successfully.', ['exercise' => $exercise]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch exercise: ' . $e->getMessage(), null, 500);
}
