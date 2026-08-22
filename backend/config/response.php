<?php
/**
 * IRONCORE GYM MANAGEMENT SYSTEM
 * Standardized Response & Authentication Helper
 */

// Enable error reporting to avoid silent failures in development
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Global JSON Header
header('Content-Type: application/json; charset=UTF-8');
// Reflect origin to allow credentials (sessions) to work
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://127.0.0.1:8000';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function start_session_safe(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function json_response(bool $success, string $message, mixed $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_json_input(): array {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
    }
    return $_POST;
}

function require_auth(array $allowedRoles = []): array {
    start_session_safe();

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        json_response(false, 'Unauthorized. Please login to continue.', null, 401);
    }

    $role = $_SESSION['role'] ?? 'member';

    if (!empty($allowedRoles) && !in_array($role, $allowedRoles)) {
        json_response(false, 'Forbidden. You do not have permission to access this resource.', null, 403);
    }

    return [
        'user_id' => $_SESSION['user_id'],
        'role'    => $role,
        'name'    => $_SESSION['name'] ?? 'User',
        'email'   => $_SESSION['email'] ?? ''
    ];
}

function get_member_id_for_user(PDO $pdo, int $userId): ?int {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE user_id = ?");
    $stmt->execute([$userId]);
    $res = $stmt->fetch();
    return $res ? (int)$res['id'] : null;
}

function get_trainer_id_for_user(PDO $pdo, int $userId): ?int {
    $stmt = $pdo->prepare("SELECT id FROM trainers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $res = $stmt->fetch();
    return $res ? (int)$res['id'] : null;
}
