<?php
/**
 * IRONCORE - Update Exercise API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$exerciseId   = !empty($input['id']) ? (int)$input['id'] : null;
$name         = trim($input['name'] ?? '');
$category     = trim($input['category'] ?? '');
$muscleGroup  = trim($input['muscle_group'] ?? '');
$difficulty   = trim($input['difficulty'] ?? '');
$sets         = !empty($input['sets']) ? (int)$input['sets'] : null;
$reps         = trim($input['reps'] ?? '');
$restTime     = trim($input['rest_time'] ?? '');
$instructions = trim($input['instructions'] ?? '');

if (!$exerciseId) {
    json_response(false, 'Exercise ID is required.');
}

try {
    $stmt = $pdo->prepare("
        UPDATE exercises SET
            name = COALESCE(NULLIF(?, ''), name),
            category = COALESCE(NULLIF(?, ''), category),
            muscle_group = COALESCE(NULLIF(?, ''), muscle_group),
            difficulty = COALESCE(NULLIF(?, ''), difficulty),
            sets = COALESCE(?, sets),
            reps = COALESCE(NULLIF(?, ''), reps),
            rest_time = COALESCE(NULLIF(?, ''), rest_time),
            instructions = COALESCE(NULLIF(?, ''), instructions)
        WHERE id = ?
    ");
    $stmt->execute([$name, $category, $muscleGroup, $difficulty, $sets, $reps, $restTime, $instructions, $exerciseId]);

    json_response(true, 'Exercise updated successfully.');

} catch (Exception $e) {
    json_response(false, 'Failed to update exercise: ' . $e->getMessage(), null, 500);
}
