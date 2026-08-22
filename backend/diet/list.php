<?php
/**
 * IRONCORE - List Diet Plans API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();

try {
    $stmt = $pdo->query("
        SELECT 
            dp.*,
            tu.name AS trainer_name,
            (SELECT COUNT(*) FROM diet_meals WHERE diet_plan_id = dp.id) AS total_meals,
            (SELECT COUNT(*) FROM member_diets WHERE diet_plan_id = dp.id AND status = 'active') AS active_members_assigned
        FROM diet_plans dp
        LEFT JOIN trainers t ON dp.trainer_id = t.id
        LEFT JOIN users tu ON t.user_id = tu.id
        ORDER BY dp.id ASC
    ");
    $plans = $stmt->fetchAll();

    json_response(true, 'Diet plans loaded successfully.', [
        'count' => count($plans),
        'plans' => $plans
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch diet plans: ' . $e->getMessage(), null, 500);
}
