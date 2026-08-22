<?php
/**
 * IRONCORE - Get Single Trainer Profile API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();
$trainerId = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (!$trainerId) {
    json_response(false, 'Trainer ID is required.');
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.id AS trainer_id,
            t.user_id,
            u.name,
            u.email,
            t.phone,
            t.specialization,
            t.experience,
            t.bio,
            t.profile_image,
            u.status,
            u.created_at
        FROM trainers t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ? LIMIT 1
    ");
    $stmt->execute([$trainerId]);
    $trainer = $stmt->fetch();

    if (!$trainer) {
        json_response(false, 'Trainer not found.', null, 404);
    }

    // Fetch assigned members
    $mStmt = $pdo->prepare("
        SELECT m.id AS member_id, m.member_code, u.name, u.email, m.phone, m.fitness_goal, m.fitness_level,
               p.name AS plan_name, ms.end_date, ms.status AS membership_status
        FROM members m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN memberships ms ON ms.member_id = m.id AND ms.status = 'active'
        LEFT JOIN membership_plans p ON ms.plan_id = p.id
        WHERE m.trainer_id = ?
        ORDER BY m.id DESC
    ");
    $mStmt->execute([$trainerId]);
    $assignedMembers = $mStmt->fetchAll();

    // Fetch trainer's workout plans
    $wpStmt = $pdo->prepare("SELECT * FROM workout_plans WHERE trainer_id = ? ORDER BY id DESC");
    $wpStmt->execute([$trainerId]);
    $workoutPlans = $wpStmt->fetchAll();

    // Fetch upcoming appointments
    $appStmt = $pdo->prepare("
        SELECT a.*, u.name AS member_name, m.phone AS member_phone, m.member_code
        FROM appointments a
        JOIN members m ON a.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE a.trainer_id = ?
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $appStmt->execute([$trainerId]);
    $appointments = $appStmt->fetchAll();

    json_response(true, 'Trainer loaded successfully.', [
        'trainer'          => $trainer,
        'assigned_members' => $assignedMembers,
        'workout_plans'    => $workoutPlans,
        'appointments'     => $appointments
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to load trainer: ' . $e->getMessage(), null, 500);
}
