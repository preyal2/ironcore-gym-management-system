<?php
/**
 * IRONCORE - Add Progress Log API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId = !empty($input['member_id']) ? (int)$input['member_id'] : null;

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

$weight = !empty($input['weight']) ? (float)$input['weight'] : null;
$waist  = !empty($input['waist']) ? (float)$input['waist'] : null;
$chest  = !empty($input['chest']) ? (float)$input['chest'] : null;
$arms   = !empty($input['arms']) ? (float)$input['arms'] : null;
$legs   = !empty($input['legs']) ? (float)$input['legs'] : null;
$notes  = trim($input['notes'] ?? 'Body measurement update');
$date   = !empty($input['record_date']) ? $input['record_date'] : date('Y-m-d');

if (!$memberId || !$weight) {
    json_response(false, 'Member ID and valid body weight (kg) are required.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO progress (member_id, weight, waist, chest, arms, legs, notes, record_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$memberId, $weight, $waist, $chest, $arms, $legs, $notes, $date]);
    $progressId = (int)$pdo->lastInsertId();

    // Update current weight in members table
    $upStmt = $pdo->prepare("UPDATE members SET weight = ? WHERE id = ?");
    $upStmt->execute([$weight, $memberId]);

    $pdo->commit();

    json_response(true, 'Body progress entry recorded successfully.', [
        'progress_id' => $progressId,
        'weight'      => $weight,
        'date'        => $date
    ], 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to save progress entry: ' . $e->getMessage(), null, 500);
}
