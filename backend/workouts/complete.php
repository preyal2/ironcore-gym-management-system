<?php
/**
 * IRONCORE - Complete Workout / Exercise API
 * Logs completion status into workout_progress table
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId          = !empty($input['member_id']) ? (int)$input['member_id'] : null;
$workoutPlanId     = !empty($input['workout_plan_id']) ? (int)$input['workout_plan_id'] : 1;
$workoutExerciseId = !empty($input['workout_exercise_id']) ? (int)$input['workout_exercise_id'] : null;
$status            = trim($input['status'] ?? 'Completed');
$notes             = trim($input['notes'] ?? 'Completed daily workout routine');
$date              = !empty($input['date']) ? $input['date'] : date('Y-m-d');

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

if (!$memberId) {
    json_response(false, 'Member ID is required.');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO workout_progress (member_id, workout_plan_id, workout_exercise_id, completion_date, status, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$memberId, $workoutPlanId, $workoutExerciseId, $date, $status, $notes]);

    // Send milestone notification if user reaches 10, 25, 50 workouts
    $cntStmt = $pdo->prepare("SELECT COUNT(*) AS total_done FROM workout_progress WHERE member_id = ? AND status = 'Completed'");
    $cntStmt->execute([$memberId]);
    $countRow = $cntStmt->fetch();
    $totalDone = (int)($countRow['total_done'] ?? 0);

    $milestones = [10 => '🔥 10 Workouts Milestone!', 25 => '🏆 25 Workouts Crushed!', 50 => '💪 50 Workouts Elite Club!'];
    if (isset($milestones[$totalDone])) {
        $uStmt = $pdo->prepare("SELECT user_id FROM members WHERE id = ?");
        $uStmt->execute([$memberId]);
        $mUser = $uStmt->fetch();
        if ($mUser) {
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'achievement')");
            $notifStmt->execute([(int)$mUser['user_id'], $milestones[$totalDone], "Congratulations! You have completed {$totalDone} full workouts with IronCore."]);
        }
    }

    json_response(true, 'Workout marked as completed! Keep up the momentum.', [
        'total_completed' => $totalDone
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to save workout completion: ' . $e->getMessage(), null, 500);
}
