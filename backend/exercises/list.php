<?php
/**
 * IRONCORE - List Exercises API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$pdo = get_db();

$category   = trim($_GET['category'] ?? '');
$difficulty = trim($_GET['difficulty'] ?? '');
$search     = trim($_GET['search'] ?? '');

try {
    $query = "SELECT * FROM exercises WHERE 1=1";
    $params = [];

    if (!empty($category) && $category !== 'All') {
        $query .= " AND category = ?";
        $params[] = $category;
    }

    if (!empty($difficulty) && $difficulty !== 'All') {
        $query .= " AND difficulty = ?";
        $params[] = $difficulty;
    }

    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR muscle_group LIKE ? OR instructions LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    $query .= " ORDER BY category ASC, name ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $exercises = $stmt->fetchAll();

    json_response(true, 'Exercises fetched successfully.', [
        'count'     => count($exercises),
        'exercises' => $exercises
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch exercises: ' . $e->getMessage(), null, 500);
}
