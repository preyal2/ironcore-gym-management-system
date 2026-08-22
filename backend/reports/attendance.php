<?php
/**
 * IRONCORE - Attendance Analytics & Report API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

try {
    $today = date('Y-m-d');
    $thirtyDaysAgo = sql_date_n_days_ago(30);
    $fourteenDaysAgo = sql_date_n_days_ago(14);

    // 1. Today's Total Checkins
    $todayStmt = $pdo->prepare("SELECT COUNT(*) AS count FROM attendance WHERE attendance_date = ?");
    $todayStmt->execute([$today]);
    $todayCount = (int)($todayStmt->fetch()['count'] ?? 0);

    // 2. Average Daily Checkins (Last 30 days)
    $avgStmt = $pdo->query("
        SELECT ROUND(AVG(day_count), 1) AS avg_daily
        FROM (
            SELECT attendance_date, COUNT(*) AS day_count
            FROM attendance
            WHERE attendance_date >= {$thirtyDaysAgo}
            GROUP BY attendance_date
        ) t
    ");
    $avgDaily = (float)($avgStmt->fetch()['avg_daily'] ?? 0);

    // 3. Daily Attendance for last 14 days (for Chart.js)
    $dailyStmt = $pdo->query("
        SELECT attendance_date, COUNT(*) AS count
        FROM attendance
        WHERE attendance_date >= {$fourteenDaysAgo}
        GROUP BY attendance_date
        ORDER BY attendance_date ASC
    ");
    $dailyTrend = $dailyStmt->fetchAll();

    // 4. Most Consistent Members (Top 10)
    $topStmt = $pdo->query("
        SELECT m.id, m.member_code, u.name, m.phone, COUNT(a.id) AS total_visits
        FROM members m
        JOIN users u ON m.user_id = u.id
        JOIN attendance a ON a.member_id = m.id
        GROUP BY m.id, m.member_code, u.name, m.phone
        ORDER BY total_visits DESC
        LIMIT 10
    ");
    $topMembers = $topStmt->fetchAll();

    json_response(true, 'Attendance report loaded.', [
        'today_count'  => $todayCount,
        'avg_daily'    => $avgDaily,
        'daily_trend'  => $dailyTrend,
        'top_members'  => $topMembers
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to load attendance report: ' . $e->getMessage(), null, 500);
}
