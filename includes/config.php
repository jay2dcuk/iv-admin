<?php
// Temporary debug
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Load env if exists
$env_file = __DIR__ . '/env.php';
if (file_exists($env_file)) require_once $env_file;

// Database — use env vars or hardcoded demo values
define('DB_HOST', getenv('DB_HOST') ?: 'mysql.iv.digital');
define('DB_NAME', getenv('DB_NAME') ?: 'ivhew_demo');
define('DB_USER', getenv('DB_USER') ?: 'ivhew_user');
define('DB_PASS', getenv('DB_PASS') ?: 'ivhew_demo');

// App
define('APP_NAME', 'Hewitts Admin');
define('APP_URL',  getenv('APP_URL') ?: 'https://demo.iv.digital/ivhew');

// Payment
define('CARDSAVE_MERCHANT_ID', getenv('CARDSAVE_MERCHANT_ID') ?: '');
define('CARDSAVE_PASSWORD',    getenv('CARDSAVE_PASSWORD')    ?: '');

// AI
define('ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY') ?: '');
define('ANTHROPIC_MODEL',   'claude-sonnet-4-6');

// Session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Helpers
function clean(string $v, int $max = 255): string {
    return substr(trim(strip_tags($v)), 0, $max);
}

function require_auth(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_verify(): void {
    $t = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(403); die('Invalid CSRF token');
    }
}
