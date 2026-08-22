<?php
/**
 * IRONCORE - List Workout Plans API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();

$goal  = trim($_GET['goal'] ?? '');
$level = trim($_GET['level'] ?? '');

try {
    $query = "
        SELECT 
            wp.*,
            tu.name AS trainer_name,
            t.specialization AS trainer_specialization,
            (SELECT COUNT(*) FROM workout_exercises WHERE workout_plan_id = wp.id) AS total_exercises
        FROM workout_plans wp
        LEFT JOIN trainers t ON wp.trainer_id = t.id
        LEFT JOIN users tu ON t.user_id = tu.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($goal) && $goal !== 'All') {
        $query .= " AND wp.goal = ?";
        $params[] = $goal;
    }

    if (!empty($level) && $level !== 'All') {
        $query .= " AND wp.fitness_level = ?";
        $params[] = $level;
    }

    $query .= " ORDER BY wp.id ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $plans = $stmt->fetchAll();

    json_response(true, 'Workout plans retrieved successfully.', [
        'count' => count($plans),
        'plans' => $plans
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to load workout plans: ' . $e->getMessage(), null, 500);
}
