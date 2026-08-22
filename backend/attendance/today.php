<?php
/**
 * IRONCORE - Today's Attendance API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

try {
    $today = date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT 
            a.id AS attendance_id,
            a.attendance_date,
            a.check_in_time,
            a.check_out_time,
            a.status,
            m.id AS member_id,
            m.member_code,
            u.name AS member_name,
            u.email AS member_email,
            m.phone AS member_phone,
            tu.name AS trainer_name,
            mp.name AS plan_name
        FROM attendance a
        JOIN members m ON a.member_id = m.id
        JOIN users u ON m.user_id = u.id
        LEFT JOIN trainers t ON m.trainer_id = t.id
        LEFT JOIN users tu ON t.user_id = tu.id
        LEFT JOIN memberships ms ON ms.member_id = m.id AND ms.status = 'active'
        LEFT JOIN membership_plans mp ON ms.plan_id = mp.id
        WHERE a.attendance_date = ?
        ORDER BY a.check_in_time DESC
    ");
    $stmt->execute([$today]);
    $records = $stmt->fetchAll();

    $activeInside = 0;
    foreach ($records as $r) {
        if (empty($r['check_out_time'])) {
            $activeInside++;
        }
    }

    json_response(true, "Today's attendance roster loaded.", [
        'date'               => $today,
        'total_checked_in'   => count($records),
        'active_now_in_gym'  => $activeInside,
        'roster'             => $records
    ]);

} catch (Exception $e) {
    json_response(false, "Failed to load today's attendance: " . $e->getMessage(), null, 500);
}
