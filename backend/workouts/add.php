<?php
/**
 * IRONCORE - Add Workout Plan API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$name        = trim($input['name'] ?? '');
$goal        = trim($input['goal'] ?? 'Muscle Building');
$level       = trim($input['fitness_level'] ?? 'Beginner');
$duration    = trim($input['duration'] ?? '4 Weeks');
$description = trim($input['description'] ?? '');
$trainerId   = !empty($input['trainer_id']) ? (int)$input['trainer_id'] : null;
$exercises   = $input['exercises'] ?? []; // Array of {exercise_id, day_name, sets, reps, rest_time}

if ($auth['role'] === 'trainer' && !$trainerId) {
    $trainerId = get_trainer_id_for_user($pdo, $auth['user_id']);
}

if (empty($name)) {
    json_response(false, 'Workout Plan Name is required.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO workout_plans (trainer_id, name, goal, fitness_level, duration, description)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$trainerId, $name, $goal, $level, $duration, $description]);
    $planId = (int)$pdo->lastInsertId();

    if (!empty($exercises) && is_array($exercises)) {
        $insExStmt = $pdo->prepare("
            INSERT INTO workout_exercises (workout_plan_id, exercise_id, day_name, sets, reps, rest_time, order_number)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $order = 1;
        foreach ($exercises as $ex) {
            $exId     = (int)($ex['exercise_id'] ?? 0);
            $day      = trim($ex['day_name'] ?? 'Monday');
            $sets     = (int)($ex['sets'] ?? 3);
            $reps     = trim($ex['reps'] ?? '10-12');
            $restTime = trim($ex['rest_time'] ?? '60 sec');

            if ($exId > 0) {
                $insExStmt->execute([$planId, $exId, $day, $sets, $reps, $restTime, $order++]);
            }
        }
    }

    $pdo->commit();

    json_response(true, 'Workout plan created successfully.', ['plan_id' => $planId], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to create workout plan: ' . $e->getMessage(), null, 500);
}
