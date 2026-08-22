<?php
/**
 * IRONCORE - Member Analytics & Reports API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

try {
    // 1. Total Members Summary
    $totStmt = $pdo->query("SELECT COUNT(*) AS total FROM members");
    $totalMembers = (int)$totStmt->fetch()['total'];

    // 2. Active Memberships
    $actStmt = $pdo->query("SELECT COUNT(DISTINCT member_id) AS active_count FROM memberships WHERE status = 'active' AND end_date >= CURRENT_DATE");
    $activeMembers = (int)$actStmt->fetch()['active_count'];

    // 3. Expiring Soon (within 14 days)
    $expSoonStmt = $pdo->query("SELECT COUNT(DISTINCT member_id) AS expiring_count FROM memberships WHERE status = 'active' AND CAST(julianday(end_date) - julianday(date('now')) AS INTEGER) BETWEEN 0 AND 14");
    $expiringSoon = (int)$expSoonStmt->fetch()['expiring_count'];

    // 4. Expired Members
    $expStmt = $pdo->query("SELECT COUNT(DISTINCT member_id) AS expired_count FROM memberships WHERE status = 'expired' OR end_date < CURRENT_DATE");
    $expiredMembers = (int)$expStmt->fetch()['expired_count'];

    // 5. Gender Distribution
    $genStmt = $pdo->query("SELECT gender, COUNT(*) AS count FROM members GROUP BY gender");
    $genderDist = $genStmt->fetchAll();

    // 6. Fitness Goals Distribution
    $goalStmt = $pdo->query("SELECT fitness_goal, COUNT(*) AS count FROM members GROUP BY fitness_goal ORDER BY count DESC");
    $goalDist = $goalStmt->fetchAll();

    // 7. Recent Registrations List
    $recStmt = $pdo->query("
        SELECT m.id, m.member_code, u.name, u.email, m.phone, m.gender, m.created_at,
               p.name AS plan_name, ms.status AS membership_status
        FROM members m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN memberships ms ON ms.member_id = m.id AND ms.status = 'active'
        LEFT JOIN membership_plans p ON ms.plan_id = p.id
        ORDER BY m.id DESC LIMIT 50
    ");
    $recentMembers = $recStmt->fetchAll();

    json_response(true, 'Member reports generated.', [
        'total_members'       => $totalMembers,
        'active_members'      => $activeMembers,
        'expiring_soon'       => $expiringSoon,
        'expired_members'     => $expiredMembers,
        'gender_distribution' => $genderDist,
        'goal_distribution'   => $goalDist,
        'members_list'        => $recentMembers
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to generate member report: ' . $e->getMessage(), null, 500);
}
