<?php
/**
 * IRONCORE - Cancel / Delete Membership API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

require_auth(['admin']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$membershipId = !empty($input['membership_id']) ? (int)$input['membership_id'] : null;

if (!$membershipId) {
    json_response(false, 'Membership ID is required.');
}

try {
    $stmt = $pdo->prepare("UPDATE memberships SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$membershipId]);

    json_response(true, 'Membership cancelled successfully.');
} catch (Exception $e) {
    json_response(false, 'Failed to cancel membership: ' . $e->getMessage(), null, 500);
}
