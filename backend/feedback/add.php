<?php
/**
 * IRONCORE - Submit Feedback API (Member)
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/response.php';

$auth = require_auth(['admin', 'member']);
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', null, 405);
}

$input = get_json_input();
$memberId = !empty($input['member_id']) ? (int)$input['member_id'] : null;

if ($auth['role'] === 'member') {
    $memberId = get_member_id_for_user($pdo, $auth['user_id']);
}

$rating   = !empty($input['rating']) ? (int)$input['rating'] : 5;
$message  = trim($input['message'] ?? '');
$category = trim($input['category'] ?? 'General');

if (!$memberId || empty($message)) {
    json_response(false, 'Rating and feedback message are required.');
}

if ($rating < 1 || $rating > 5) {
    $rating = 5;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO feedback (member_id, rating, message, category)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$memberId, $rating, $message, $category]);

    json_response(true, 'Thank you for your valuable feedback!', null, 201);

} catch (Exception $e) {
    json_response(false, 'Failed to submit feedback: ' . $e->getMessage(), null, 500);
}
