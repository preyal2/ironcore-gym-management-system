<?php
/**
 * IRONCORE - Assign Diet Plan API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId   = !empty($input['member_id']) ? (int)$input['member_id'] : null;
$dietPlanId = !empty($input['diet_plan_id']) ? (int)$input['diet_plan_id'] : null;

if (!$memberId || !$dietPlanId) {
    json_response(false, 'Member ID and Diet Plan ID are required.');
}

try {
    $pdo->beginTransaction();

    // Deactivate previous active diets for member
    $deactStmt = $pdo->prepare("UPDATE member_diets SET status = 'inactive' WHERE member_id = ?");
    $deactStmt->execute([$memberId]);

    // Insert new assignment
    $insStmt = $pdo->prepare("
        INSERT INTO member_diets (member_id, diet_plan_id, assigned_date, status)
        VALUES (?, ?, CURRENT_DATE, 'active')
    ");
    $insStmt->execute([$memberId, $dietPlanId]);

    // Send notification to member
    $mStmt = $pdo->prepare("SELECT user_id FROM members WHERE id = ?");
    $mStmt->execute([$memberId]);
    $mUser = $mStmt->fetch();
    if ($mUser) {
        $dpStmt = $pdo->prepare("SELECT name FROM diet_plans WHERE id = ?");
        $dpStmt->execute([$dietPlanId]);
        $dp = $dpStmt->fetch();
        $planName = $dp ? $dp['name'] : 'Nutrition';

        $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'New Diet Plan Assigned', ?, 'diet')");
        $nStmt->execute([(int)$mUser['user_id'], "Your trainer has assigned the '{$planName}' meal plan to your profile."]);
    }

    $pdo->commit();

    json_response(true, 'Diet plan assigned to member successfully.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to assign diet plan: ' . $e->getMessage(), null, 500);
}
