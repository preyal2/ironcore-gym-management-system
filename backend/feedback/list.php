<?php
/**
 * IRONCORE - List Feedback API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();

try {
    $stmt = $pdo->query("
        SELECT 
            f.*,
            m.member_code,
            u.name AS member_name,
            m.profile_image
        FROM feedback f
        JOIN members m ON f.member_id = m.id
        JOIN users u ON m.user_id = u.id
        ORDER BY f.created_at DESC
    ");
    $feedbacks = $stmt->fetchAll();

    // Average rating calculation
    $avgRating = 0;
    if (!empty($feedbacks)) {
        $totalStars = array_sum(array_column($feedbacks, 'rating'));
        $avgRating = round($totalStars / count($feedbacks), 1);
    }

    json_response(true, 'Feedback loaded.', [
        'average_rating' => $avgRating,
        'count'          => count($feedbacks),
        'feedback'       => $feedbacks
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch feedback: ' . $e->getMessage(), null, 500);
}
