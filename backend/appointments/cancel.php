<?php
/**
 * IRONCORE - Cancel Appointment API
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'trainer', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$appointmentId = !empty($input['id']) ? (int)$input['id'] : null;

if (!$appointmentId) {
    json_response(false, 'Appointment ID is required.');
}

try {
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
    $stmt->execute([$appointmentId]);

    json_response(true, 'Appointment cancelled successfully.');

} catch (Exception $e) {
    json_response(false, 'Failed to cancel appointment: ' . $e->getMessage(), null, 500);
}
