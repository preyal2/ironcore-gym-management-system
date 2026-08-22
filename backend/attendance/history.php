<?php
/**
 * IRONCORE - Attendance History & Stats API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$memberId  = isset($_GET['member_id']) && !empty($_GET['member_id']) ? (int)$_GET['member_id'] : null;
$startDate = trim($_GET['start_date'] ?? '');
$endDate   = trim($_GET['end_date'] ?? '');
$month     = trim($_GET['month'] ?? ''); // YYYY-MM

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

try {
    $query = "
        SELECT 
            a.*,
            m.member_code,
            u.name AS member_name
        FROM attendance a
        JOIN members m ON a.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE 1=1
    ";

    $params = [];

    if ($memberId) {
        $query .= " AND a.member_id = ?";
        $params[] = $memberId;
    }

    if (!empty($month)) {
        $query .= " AND a.attendance_date LIKE ?";
        $params[] = "{$month}%";
    } elseif (!empty($startDate) && !empty($endDate)) {
        $query .= " AND a.attendance_date BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
    }

    $query .= " ORDER BY a.attendance_date DESC, a.check_in_time DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $history = $stmt->fetchAll();

    // Calculate Streak & Monthly Percentage for single member view
    $stats = [
        'total_visits'   => count($history),
        'current_streak' => 0,
        'attendance_pct' => 0
    ];

    if ($memberId) {
        // Calculate consecutive streak days ending today or yesterday
        $streak = 0;
        $checkDate = new DateTime();
        $dateMap = [];
        foreach ($history as $h) {
            $dateMap[$h['attendance_date']] = true;
        }

        // If not attended today, start check from yesterday
        $todayStr = $checkDate->format('Y-m-d');
        if (!isset($dateMap[$todayStr])) {
            $checkDate->modify('-1 day');
        }

        while (isset($dateMap[$checkDate->format('Y-m-d')])) {
            $streak++;
            $checkDate->modify('-1 day');
        }
        $stats['current_streak'] = $streak;

        // Last 30 Days Attendance Percentage
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $last30Count = 0;
        foreach ($history as $h) {
            if ($h['attendance_date'] >= $thirtyDaysAgo) {
                $last30Count++;
            }
        }
        $stats['attendance_pct'] = min(100, round(($last30Count / 24) * 100)); // standard 24 gym days/month
    }

    json_response(true, 'Attendance history fetched.', [
        'stats'   => $stats,
        'count'   => count($history),
        'history' => $history
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch attendance history: ' . $e->getMessage(), null, 500);
}
