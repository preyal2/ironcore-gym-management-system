<?php
/**
 * IRONCORE - Progress Summary API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$memberId = isset($_GET['member_id']) && !empty($_GET['member_id']) ? (int)$_GET['member_id'] : null;

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

if (!$memberId) {
    json_response(false, 'Member ID is required.');
}

try {
    // 1. Fetch First and Latest Progress logs
    $pStmt = $pdo->prepare("SELECT * FROM progress WHERE member_id = ? ORDER BY record_date ASC, id ASC");
    $pStmt->execute([$memberId]);
    $logs = $pStmt->fetchAll();

    $startWeight = null;
    $currentWeight = null;
    $weightDiff = 0.00;

    if (!empty($logs)) {
        $startWeight = (float)$logs[0]['weight'];
        $currentWeight = (float)$logs[count($logs) - 1]['weight'];
        $weightDiff = round($currentWeight - $startWeight, 2);
    } else {
        // Fallback to member profile weight
        $mStmt = $pdo->prepare("SELECT weight FROM members WHERE id = ?");
        $mStmt->execute([$memberId]);
        $mRow = $mStmt->fetch();
        if ($mRow && $mRow['weight']) {
            $startWeight = (float)$mRow['weight'];
            $currentWeight = (float)$mRow['weight'];
        }
    }

    // 2. Total Workouts Completed
    $wStmt = $pdo->prepare("SELECT COUNT(*) AS total_workouts FROM workout_progress WHERE member_id = ? AND status = 'Completed'");
    $wStmt->execute([$memberId]);
    $wRow = $wStmt->fetch();
    $totalWorkouts = (int)($wRow['total_workouts'] ?? 0);

    // 3. Attendance Rate
    $aStmt = $pdo->prepare("SELECT COUNT(*) AS total_attendance FROM attendance WHERE member_id = ?");
    $aStmt->execute([$memberId]);
    $aRow = $aStmt->fetch();
    $totalAttendance = (int)($aRow['total_attendance'] ?? 0);

    // 4. Milestone streak
    $streak = min($totalAttendance, 14); // Demo calculated streak

    json_response(true, 'Progress summary loaded.', [
        'starting_weight'    => $startWeight,
        'current_weight'     => $currentWeight,
        'weight_change'      => $weightDiff,
        'total_workouts'     => $totalWorkouts,
        'total_attendance'   => $totalAttendance,
        'fitness_streak_days'=> $streak,
        'recent_logs'        => array_slice($logs, -10)
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to compute progress summary: ' . $e->getMessage(), null, 500);
}
