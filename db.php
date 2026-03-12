<?php
if (defined('DB_INITIALIZED')) return;
define('DB_INITIALIZED', true);
ini_set('session.gc_maxlifetime', 86400);    
session_set_cookie_params([
    'lifetime' => 86400,                  
    'path'     => '/',
    'secure'   => true,                    
    'httponly' => true,                      
    'samesite' => 'Lax'
]);
session_start();

$env_file = __DIR__ . '/.env.php';
$env = file_exists($env_file) ? require $env_file : [];

define('DB_SERVER', $env['DB_SERVER'] ?? 'localhost');
define('DB_USER',   $env['DB_USER'] ?? 'root');
define('DB_PASS',   $env['DB_PASS'] ?? 'root');
define('DB_NAME',   $env['DB_NAME'] ?? 'root');

$connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if (isset($_SESSION['db_session_id'])) {
    // Validate the session hasn't expired in DB
    $sid = $_SESSION['db_session_id'];
    $stmt = $connection->prepare(
        "SELECT id FROM sessions WHERE session_id = ? AND expires_at > NOW()"
    );
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Session expired in DB — destroy and restart
        session_destroy();
        header('Location: login.php');
        exit;
    }
} else {
    // New session — insert into DB
    $session_id  = session_id();
    $expires_at  = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $stmt = $connection->prepare(
        "INSERT INTO sessions (session_id, expires_at) VALUES (?, ?)"
    );
    $stmt->bind_param("ss", $session_id, $expires_at);
    $stmt->execute();
    $_SESSION['db_session_id'] = $session_id;
}

?>