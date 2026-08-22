<?php
/**
 * IRONCORE - List Announcements API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();

start_session_safe();
$role = $_SESSION['role'] ?? 'all';

try {
    $query = "SELECT * FROM announcements WHERE 1=1";
    $params = [];

    if ($role !== 'admin') {
        $query .= " AND (target_role = 'all' OR target_role = ?)";
        $params[] = $role;
    }

    $query .= " ORDER BY created_at DESC LIMIT 20";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $announcements = $stmt->fetchAll();

    json_response(true, 'Announcements fetched successfully.', [
        'count'         => count($announcements),
        'announcements' => $announcements
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch announcements: ' . $e->getMessage(), null, 500);
}
