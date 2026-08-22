<?php
/**
 * IRONCORE - List Appointments API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$status    = trim($_GET['status'] ?? '');
$trainerId = isset($_GET['trainer_id']) && !empty($_GET['trainer_id']) ? (int)$_GET['trainer_id'] : null;
$memberId  = isset($_GET['member_id']) && !empty($_GET['member_id']) ? (int)$_GET['member_id'] : null;

// Role-based restrictions
if ($auth['role'] === 'trainer' && !$trainerId) {
    $trainerId = get_trainer_id_for_user($pdo, $auth['user_id']);
} elseif ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

try {
    $query = "
        SELECT 
            a.*,
            m.member_code,
            u.name AS member_name,
            u.email AS member_email,
            m.phone AS member_phone,
            tu.name AS trainer_name,
            tu.email AS trainer_email,
            t.phone AS trainer_phone,
            t.specialization AS trainer_specialization
        FROM appointments a
        JOIN members m ON a.member_id = m.id
        JOIN users u ON m.user_id = u.id
        JOIN trainers t ON a.trainer_id = t.id
        JOIN users tu ON t.user_id = tu.id
        WHERE 1=1
    ";

    $params = [];

    if ($trainerId) {
        $query .= " AND a.trainer_id = ?";
        $params[] = $trainerId;
    }

    if ($memberId) {
        $query .= " AND a.member_id = ?";
        $params[] = $memberId;
    }

    if (!empty($status) && $status !== 'All') {
        $query .= " AND a.status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();

    json_response(true, 'Appointments retrieved.', [
        'count'        => count($appointments),
        'appointments' => $appointments
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch appointments: ' . $e->getMessage(), null, 500);
}
