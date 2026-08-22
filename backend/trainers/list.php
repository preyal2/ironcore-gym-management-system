<?php
/**
 * IRONCORE - List Trainers API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();

try {
    $stmt = $pdo->query("
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
            (SELECT COUNT(*) FROM members WHERE trainer_id = t.id) AS assigned_members_count,
            (SELECT COUNT(*) FROM workout_plans WHERE trainer_id = t.id) AS workout_plans_count
        FROM trainers t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.id ASC
    ");
    $trainers = $stmt->fetchAll();

    json_response(true, 'Trainers fetched successfully.', [
        'count'    => count($trainers),
        'trainers' => $trainers
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch trainers: ' . $e->getMessage(), null, 500);
}
