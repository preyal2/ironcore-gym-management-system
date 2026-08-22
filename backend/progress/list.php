<?php
/**
 * IRONCORE - List Progress Logs API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

$memberId = isset($_GET['member_id']) && !empty($_GET['member_id']) ? (int)$_GET['member_id'] : null;

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

if (!$memberId) {
    json_response(false, 'Member ID is required.');
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM progress
        WHERE member_id = ?
        ORDER BY record_date ASC, id ASC
    ");
    $stmt->execute([$memberId]);
    $logs = $stmt->fetchAll();

    json_response(true, 'Progress records loaded.', [
        'count' => count($logs),
        'logs'  => $logs
    ]);

} catch (Exception $e) {
    json_response(false, 'Failed to fetch progress logs: ' . $e->getMessage(), null, 500);
}
