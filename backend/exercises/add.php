<?php
/**
 * IRONCORE - Add Exercise API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$name         = trim($input['name'] ?? '');
$category     = trim($input['category'] ?? 'Chest');
$muscleGroup  = trim($input['muscle_group'] ?? '');
$difficulty   = trim($input['difficulty'] ?? 'Beginner');
$sets         = !empty($input['sets']) ? (int)$input['sets'] : 3;
$reps         = trim($input['reps'] ?? '10-12');
$restTime     = trim($input['rest_time'] ?? '60 sec');
$instructions = trim($input['instructions'] ?? '');

if (empty($name) || empty($muscleGroup)) {
    json_response(false, 'Exercise Name and Targeted Muscle Group are required.');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO exercises (name, category, muscle_group, difficulty, sets, reps, rest_time, instructions)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $category, $muscleGroup, $difficulty, $sets, $reps, $restTime, $instructions]);
    $exerciseId = (int)$pdo->lastInsertId();

    json_response(true, 'Exercise added to library successfully.', ['exercise_id' => $exerciseId], 201);

} catch (Exception $e) {
    json_response(false, 'Failed to add exercise: ' . $e->getMessage(), null, 500);
}
