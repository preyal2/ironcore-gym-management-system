<?php
/**
 * IRONCORE - Get Workout Plan Details API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();
$planId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (!$planId) {
    json_response(false, 'Workout Plan ID is required.');
}

try {
    // Fetch Plan Details
    $stmt = $pdo->prepare("
        SELECT wp.*, tu.name AS trainer_name
        FROM workout_plans wp
        LEFT JOIN trainers t ON wp.trainer_id = t.id
        LEFT JOIN users tu ON t.user_id = tu.id
        WHERE wp.id = ? LIMIT 1
    ");
    $stmt->execute([$planId]);
    $plan = $stmt->fetch();

    if (!$plan) {
        json_response(false, 'Workout plan not found.', null, 404);
    }

    // Fetch Plan Exercises grouped by Day
    $eStmt = $pdo->prepare("
        SELECT 
            we.id AS workout_exercise_id,
            we.day_name,
            we.sets AS plan_sets,
            we.reps AS plan_reps,
            we.rest_time AS plan_rest,
            we.order_number,
            e.id AS exercise_id,
            e.name AS exercise_name,
            e.category,
            e.muscle_group,
            e.difficulty,
            e.instructions,
            e.image
        FROM workout_exercises we
        JOIN exercises e ON we.exercise_id = e.id
        WHERE we.workout_plan_id = ?
        ORDER BY CASE we.day_name WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 WHEN 'Sunday' THEN 7 ELSE 8 END, we.order_number ASC
    ");
    $eStmt->execute([$planId]);
    $exercises = $eStmt->fetchAll();

    // Group exercises by day
    $days = [
        'Monday'    => [],
        'Tuesday'   => [],
        'Wednesday' => [],
        'Thursday'  => [],
        'Friday'    => [],
        'Saturday'  => [],
        'Sunday'    => []
    ];

    foreach ($exercises as $ex) {
        $days[$ex['day_name']][] = $ex;
    }

    json_response(true, 'Workout plan loaded successfully.', [
        'plan'      => $plan,
        'exercises' => $exercises,
        'schedule'  => $days
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch workout plan: ' . $e->getMessage(), null, 500);
}
