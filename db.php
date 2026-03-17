<?php
if (defined('DB_INITIALIZED')) return;
define('DB_INITIALIZED', true);

// ─── 1. Session config must come BEFORE session_start ───────────────────────
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params([
    'lifetime' => 86400,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// ─── 2. Start session only for browser requests (not API calls) ─────────────
if (!defined('API_REQUEST') && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── 3. DB connection ────────────────────────────────────────────────────────
$env_file = __DIR__ . '/.env.php';
$env = file_exists($env_file) ? require $env_file : [];

define('DB_SERVER', $env['DB_SERVER'] ?? 'localhost');
define('DB_USER',   $env['DB_USER']   ?? 'cp3282');
define('DB_PASS',   $env['DB_PASS']   ?? 'WZU8qQPgdwXPxmG0');
define('DB_NAME',   $env['DB_NAME']   ?? 'cp3282_db');

$connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

// ─── 4. DB session tracking (only for browser sessions) ─────────────────────
if (!defined('API_REQUEST') && session_status() === PHP_SESSION_ACTIVE) {
    $session_id = session_id();

    if (!empty($_SESSION['db_session_id'])) {
        // Returning visitor — refresh the expiry instead of redirecting to login
        $stmt = $connection->prepare(
            "UPDATE sessions SET expires_at = ? WHERE session_id = ?"
        );
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $stmt->bind_param("ss", $expires_at, $session_id);
        $stmt->execute();

    } else {
        // First visit or cookie was cleared — upsert to avoid duplicate key errors
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $stmt = $connection->prepare(
            "INSERT INTO sessions (session_id, expires_at) 
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE expires_at = VALUES(expires_at)"
        );
        $stmt->bind_param("ss", $session_id, $expires_at);
        $stmt->execute();
        $_SESSION['db_session_id'] = $session_id;
    }
}
?>