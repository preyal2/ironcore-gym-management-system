<?php
/**
 * IRONCORE - List Members API
 * Allows Admin and Trainers to view and filter gym members
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer']);
$pdo = get_db();

$search     = trim($_GET['search'] ?? '');
$status     = trim($_GET['status'] ?? '');       // active, expiring_soon, expired, all
$trainerId  = isset($_GET['trainer_id']) && $_GET['trainer_id'] !== '' ? (int)$_GET['trainer_id'] : null;
$planId     = isset($_GET['plan_id']) && $_GET['plan_id'] !== '' ? (int)$_GET['plan_id'] : null;
$gender     = trim($_GET['gender'] ?? '');
$goal       = trim($_GET['goal'] ?? '');

// If trainer is logged in and not admin, default or filter to trainer's assigned members if requested
if ($auth['role'] === 'trainer' && isset($_GET['my_members_only']) && $_GET['my_members_only'] === '1') {
    $currentTrainerId = get_trainer_id_for_user($pdo, $auth['user_id']);
    $trainerId = $currentTrainerId;
}

try {
    $query = "
        SELECT 
            m.id AS member_id,
            m.member_code,
            u.id AS user_id,
            u.name,
            u.email,
            m.phone,
            m.gender,
            m.date_of_birth,
            m.height,
            m.weight,
            m.fitness_goal,
            m.fitness_level,
            m.trainer_id,
            tu.name AS trainer_name,
            ms.id AS membership_id,
            p.id AS plan_id,
            p.name AS plan_name,
            p.price AS plan_price,
            ms.start_date,
            ms.end_date,
            COALESCE(ms.status, 'none') AS membership_status,
            CAST(julianday(ms.end_date) - julianday(date('now')) AS INTEGER) AS days_remaining,
            m.created_at
        FROM members m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN trainers t ON m.trainer_id = t.id
        LEFT JOIN users tu ON t.user_id = tu.id
        LEFT JOIN (
            SELECT m1.*
            FROM memberships m1
            INNER JOIN (
                SELECT member_id, MAX(id) AS max_id
                FROM memberships
                GROUP BY member_id
            ) m2 ON m1.id = m2.max_id
        ) ms ON ms.member_id = m.id
        LEFT JOIN membership_plans p ON ms.plan_id = p.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR m.phone LIKE ? OR m.member_code LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if ($trainerId !== null) {
        $query .= " AND m.trainer_id = ?";
        $params[] = $trainerId;
    }

    if ($planId !== null) {
        $query .= " AND ms.plan_id = ?";
        $params[] = $planId;
    }

    if (!empty($gender)) {
        $query .= " AND m.gender = ?";
        $params[] = $gender;
    }

    if (!empty($goal)) {
        $query .= " AND m.fitness_goal LIKE ?";
        $params[] = "%{$goal}%";
    }

    if (!empty($status) && $status !== 'all') {
        if ($status === 'active') {
            $query .= " AND ms.status = 'active' AND (ms.end_date >= CURRENT_DATE OR ms.end_date IS NULL)";
        } elseif ($status === 'expiring_soon') {
            $query .= " AND ms.status = 'active' AND CAST(julianday(ms.end_date) - julianday(date('now')) AS INTEGER) BETWEEN 0 AND 14";
        } elseif ($status === 'expired') {
            $query .= " AND (ms.status = 'expired' OR ms.end_date < CURRENT_DATE)";
        }
    }

    $query .= " ORDER BY m.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $members = $stmt->fetchAll();

    json_response(true, 'Members retrieved successfully.', [
        'count'   => count($members),
        'members' => $members
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch members: ' . $e->getMessage(), null, 500);
}
