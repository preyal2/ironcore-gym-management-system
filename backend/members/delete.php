<?php
/**
 * IRONCORE - Delete Member API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId = !empty($input['member_id']) ? (int)$input['member_id'] : null;

if (!$memberId) {
    json_response(false, 'Member ID is required.');
}

try {
    // Find user ID
    $stmt = $pdo->prepare("SELECT user_id FROM members WHERE id = ?");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();

    if (!$member) {
        json_response(false, 'Member not found.', null, 404);
    }

    $pdo->beginTransaction();

    // Deleting from users cascades to members, memberships, payments, attendance, etc.
    $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delStmt->execute([(int)$member['user_id']]);

    $pdo->commit();

    json_response(true, 'Member and associated records deleted successfully.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, 'Failed to delete member: ' . $e->getMessage(), null, 500);
}
