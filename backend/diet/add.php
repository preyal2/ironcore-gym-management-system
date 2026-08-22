<?php
/**
 * IRONCORE - Add Diet Plan API
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
$calories    = !empty($input['target_calories']) ? (int)$input['target_calories'] : 2000;
$description = trim($input['description'] ?? '');
$trainerId   = !empty($input['trainer_id']) ? (int)$input['trainer_id'] : null;
$meals       = $input['meals'] ?? []; // Array of {meal_type, food_items, calories, protein_g, carbs_g, fats_g, notes}

if ($auth['role'] === 'trainer' && !$trainerId) {
    $trainerId = get_trainer_id_for_user($pdo, $auth['user_id']);
}

if (empty($name)) {
    json_response(false, 'Diet Plan Name is required.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO diet_plans (trainer_id, name, goal, target_calories, description)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$trainerId, $name, $goal, $calories, $description]);
    $dietId = (int)$pdo->lastInsertId();

    if (!empty($meals) && is_array($meals)) {
        $mStmt = $pdo->prepare("
            INSERT INTO diet_meals (diet_plan_id, meal_type, food_items, calories, protein_g, carbs_g, fats_g, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($meals as $m) {
            $mStmt->execute([
                $dietId,
                $m['meal_type'] ?? 'Breakfast',
                $m['food_items'] ?? '',
                !empty($m['calories']) ? (int)$m['calories'] : 400,
                !empty($m['protein_g']) ? (int)$m['protein_g'] : 25,
                !empty($m['carbs_g']) ? (int)$m['carbs_g'] : 50,
                !empty($m['fats_g']) ? (int)$m['fats_g'] : 15,
                $m['notes'] ?? ''
            ]);
        }
    }

    $pdo->commit();

    json_response(true, 'Diet plan created successfully.', ['diet_plan_id' => $dietId], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to create diet plan: ' . $e->getMessage(), null, 500);
}
