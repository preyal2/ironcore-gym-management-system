<?php
/**
 * IRONCORE - Membership Plans API
 * GET: Retrieve all active plans
 * POST: Create/Update plan (Admin)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_auth(['admin']);
    $input = get_json_input();
    
    $planId       = !empty($input['id']) ? (int)$input['id'] : null;
    $name         = trim($input['name'] ?? '');
    $duration     = trim($input['duration'] ?? '');
    $durationDays = !empty($input['duration_days']) ? (int)$input['duration_days'] : 30;
    $price        = !empty($input['price']) ? (float)$input['price'] : 0.00;
    $description  = trim($input['description'] ?? '');
    $features     = trim($input['features'] ?? '');
    $status       = trim($input['status'] ?? 'active');

    if (empty($name) || $price <= 0) {
        json_response(false, 'Plan name and valid price are required.');
    }

    try {
        if ($planId) {
            $stmt = $pdo->prepare("
                UPDATE membership_plans SET
                    name = ?, duration = ?, duration_days = ?, price = ?, description = ?, features = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $duration, $durationDays, $price, $description, $features, $status, $planId]);
            json_response(true, 'Membership plan updated successfully.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO membership_plans (name, duration, duration_days, price, description, features, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $duration, $durationDays, $price, $description, $features, $status]);
            json_response(true, 'New membership plan created.', ['plan_id' => (int)$pdo->lastInsertId()], 201);
        }
    } catch (Exception $e) {
        json_response(false, 'Failed to save membership plan: ' . $e->getMessage(), null, 500);
    }
}

// GET: List all plans
try {
    $stmt = $pdo->query("SELECT * FROM membership_plans ORDER BY price ASC");
    $plans = $stmt->fetchAll();

    json_response(true, 'Membership plans retrieved.', [
        'count' => count($plans),
        'plans' => $plans
    ]);
} catch (Exception $e) {
    json_response(false, 'Failed to fetch plans: ' . $e->getMessage(), null, 500);
}
