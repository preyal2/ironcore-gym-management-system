<?php
/**
 * IRONCORE - Logout API
 * Clears and destroys active session
 */

require_once dirname(__DIR__) . '/config/response.php';

start_session_safe();

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

json_response(true, 'Logged out successfully.', [
    'redirect' => 'login.html'
]);
