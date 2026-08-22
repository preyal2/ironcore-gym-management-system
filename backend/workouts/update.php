<?php
/**
 * IRONCORE - Update Workout Plan API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$planId      = !empty($input['id']) ? (int)$input['id'] : null;
$name        = trim($input['name'] ?? '');
$goal        = trim($input['goal'] ?? '');
$level       = trim($input['fitness_level'] ?? '');
$duration    = trim($input['duration'] ?? '');
$description = trim($input['description'] ?? '');
$exercises   = $input['exercises'] ?? null;

if (!$planId) {
    json_response(false, 'Workout Plan ID is required.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE workout_plans SET
            name = COALESCE(NULLIF(?, ''), name),
            goal = COALESCE(NULLIF(?, ''), goal),
            fitness_level = COALESCE(NULLIF(?, ''), fitness_level),
            duration = COALESCE(NULLIF(?, ''), duration),
            description = COALESCE(NULLIF(?, ''), description)
        WHERE id = ?
    ");
    $stmt->execute([$name, $goal, $level, $duration, $description, $planId]);

    // If exercises array passed, replace workout exercises
    if ($exercises !== null && is_array($exercises)) {
        $delStmt = $pdo->prepare("DELETE FROM workout_exercises WHERE workout_plan_id = ?");
        $delStmt->execute([$planId]);

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

    json_response(true, 'Workout plan updated successfully.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to update workout plan: ' . $e->getMessage(), null, 500);
}
