<?php
/**
 * IRONCORE - Get Single Member Profile API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$memberId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

// If a member is requesting, ensure they only access their own profile
if ($auth['role'] === 'member') {
    $currentMemberId = get_member_id_for_user($pdo, $auth['user_id']);
    if (!$memberId || $memberId !== $currentMemberId) {
        $memberId = $currentMemberId;
    }
}

if (!$memberId) {
    json_response(false, 'Member ID is required.');
}

try {
    $today = date('Y-m-d');
    $datediff = sql_datediff('ms.end_date', "'{$today}'");
    $thirtyDaysAgo = sql_date_n_days_ago(30);

    // 1. Fetch Member Profile
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            u.name,
            u.email,
            u.status AS user_status,
            u.created_at AS registered_date,
            tu.name AS trainer_name,
            t.specialization AS trainer_specialization,
            t.phone AS trainer_phone
        FROM members m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN trainers t ON m.trainer_id = t.id
        LEFT JOIN users tu ON t.user_id = tu.id
        WHERE m.id = ? LIMIT 1
    ");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();

    if (!$member) {
        json_response(false, 'Member not found.', null, 404);
    }

    // 2. Fetch Active & Historical Memberships
    $mStmt = $pdo->prepare("
        SELECT ms.*, p.name AS plan_name, p.duration, p.price, p.features,
               {$datediff} AS days_left
        FROM memberships ms
        JOIN membership_plans p ON ms.plan_id = p.id
        WHERE ms.member_id = ?
        ORDER BY ms.id DESC
    ");
    $mStmt->execute([$memberId]);
    $memberships = $mStmt->fetchAll();

    // 3. Fetch Attendance Stats & Recent Logs
    $attStmt = $pdo->prepare("
        SELECT * FROM attendance
        WHERE member_id = ?
        ORDER BY attendance_date DESC
        LIMIT 30
    ");
    $attStmt->execute([$memberId]);
    $attendanceLogs = $attStmt->fetchAll();

    $attCountStmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total_attended,
            SUM(CASE WHEN attendance_date >= {$thirtyDaysAgo} THEN 1 ELSE 0 END) AS last_30_days_count
        FROM attendance
        WHERE member_id = ?
    ");
    $attCountStmt->execute([$memberId]);
    $attStats = $attCountStmt->fetch();

    // 4. Fetch Body Progress Logs
    $progStmt = $pdo->prepare("
        SELECT * FROM progress
        WHERE member_id = ?
        ORDER BY record_date ASC
    ");
    $progStmt->execute([$memberId]);
    $progressLogs = $progStmt->fetchAll();

    // 5. Fetch Active Assigned Diet Plan
    $mealOrder = db_driver() === 'sqlite'
        ? "CASE dm.meal_type WHEN 'Breakfast' THEN 1 WHEN 'Lunch' THEN 2 WHEN 'Snack' THEN 3 WHEN 'Dinner' THEN 4 ELSE 5 END"
        : "FIELD(dm.meal_type, 'Breakfast', 'Lunch', 'Snack', 'Dinner')";

    $dietStmt = $pdo->prepare("
        SELECT md.assigned_date, dp.name AS diet_name, dp.goal AS diet_goal, dp.target_calories, dp.description,
               dm.meal_type, dm.food_items, dm.calories, dm.protein_g, dm.carbs_g, dm.fats_g, dm.notes AS meal_notes
        FROM member_diets md
        JOIN diet_plans dp ON md.diet_plan_id = dp.id
        LEFT JOIN diet_meals dm ON dm.diet_plan_id = dp.id
        WHERE md.member_id = ? AND md.status = 'active'
        ORDER BY {$mealOrder}
    ");
    $dietStmt->execute([$memberId]);
    $dietRecords = $dietStmt->fetchAll();

    // Group meals under diet
    $dietPlan = null;
    if (!empty($dietRecords)) {
        $dietPlan = [
            'name'            => $dietRecords[0]['diet_name'],
            'goal'            => $dietRecords[0]['diet_goal'],
            'target_calories' => $dietRecords[0]['target_calories'],
            'description'     => $dietRecords[0]['description'],
            'assigned_date'   => $dietRecords[0]['assigned_date'],
            'meals'           => []
        ];
        foreach ($dietRecords as $r) {
            if ($r['meal_type']) {
                $dietPlan['meals'][] = [
                    'meal_type'  => $r['meal_type'],
                    'food_items' => $r['food_items'],
                    'calories'   => $r['calories'],
                    'protein_g'  => $r['protein_g'],
                    'carbs_g'    => $r['carbs_g'],
                    'fats_g'     => $r['fats_g'],
                    'notes'      => $r['meal_notes']
                ];
            }
        }
    }

    // 6. Fetch Payments
    $payStmt = $pdo->prepare("
        SELECT p.*, ms.plan_id, mp.name AS plan_name
        FROM payments p
        LEFT JOIN memberships ms ON p.membership_id = ms.id
        LEFT JOIN membership_plans mp ON ms.plan_id = mp.id
        WHERE p.member_id = ?
        ORDER BY p.payment_date DESC
    ");
    $payStmt->execute([$memberId]);
    $payments = $payStmt->fetchAll();

    // 7. Today's attendance check
    $todayStmt = $pdo->prepare("SELECT * FROM attendance WHERE member_id = ? AND attendance_date = ? LIMIT 1");
    $todayStmt->execute([$memberId, $today]);
    $todayAttendance = $todayStmt->fetch();

    json_response(true, 'Member profile loaded successfully.', [
        'profile'          => $member,
        'current_plan'     => $memberships[0] ?? null,
        'membership_list'  => $memberships,
        'attendance_stats' => $attStats,
        'attendance_logs'  => $attendanceLogs,
        'today_attendance' => $todayAttendance,
        'progress_logs'    => $progressLogs,
        'diet_plan'        => $dietPlan,
        'payments'         => $payments
    ]);

} catch (Exception $e) {
    json_response(false, 'Error fetching member profile: ' . $e->getMessage(), null, 500);
}
