<?php
/**
 * IRONCORE - Get Single Diet Plan API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();
$dietId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (!$dietId) {
    json_response(false, 'Diet Plan ID is required.');
}

try {
    $stmt = $pdo->prepare("
        SELECT dp.*, tu.name AS trainer_name
        FROM diet_plans dp
        LEFT JOIN trainers t ON dp.trainer_id = t.id
        LEFT JOIN users tu ON t.user_id = tu.id
        WHERE dp.id = ? LIMIT 1
    ");
    $stmt->execute([$dietId]);
    $plan = $stmt->fetch();

    if (!$plan) {
        json_response(false, 'Diet plan not found.', null, 404);
    }

    $mStmt = $pdo->prepare("
        SELECT * FROM diet_meals
        WHERE diet_plan_id = ?
        ORDER BY CASE meal_type WHEN 'Breakfast' THEN 1 WHEN 'Lunch' THEN 2 WHEN 'Snack' THEN 3 WHEN 'Dinner' THEN 4 ELSE 5 END
    ");
    $mStmt->execute([$dietId]);
    $meals = $mStmt->fetchAll();

    json_response(true, 'Diet plan retrieved.', [
        'plan'  => $plan,
        'meals' => $meals
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch diet plan: ' . $e->getMessage(), null, 500);
}
