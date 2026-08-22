<?php
/**
 * IRONCORE - Membership Plan Distribution & Analytics API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin', 'trainer']);
$pdo = get_db();

try {
    // 1. Memberships count per plan
    $stmt = $pdo->query("
        SELECT 
            p.id AS plan_id,
            p.name AS plan_name,
            p.price,
            p.duration,
            COUNT(ms.id) AS total_enrolled,
            SUM(CASE WHEN ms.status = 'active' AND ms.end_date >= CURRENT_DATE THEN 1 ELSE 0 END) AS active_subscribers,
            COALESCE(SUM(p.price), 0) AS estimated_plan_revenue
        FROM membership_plans p
        LEFT JOIN memberships ms ON ms.plan_id = p.id
        GROUP BY p.id
        ORDER BY active_subscribers DESC
    ");
    $planStats = $stmt->fetchAll();

    json_response(true, 'Membership analytics loaded.', [
        'plan_stats' => $planStats
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch membership report: ' . $e->getMessage(), null, 500);
}
