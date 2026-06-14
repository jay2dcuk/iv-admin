<?php
// ── Database ──────────────────────────────────────────────────────
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'ivhew');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// ── App ───────────────────────────────────────────────────────────
define('APP_NAME', 'Hewitts Admin');
define('APP_URL',  getenv('APP_URL') ?: 'https://demo.iv.digital/ivhew');

// ── Payment (from environment only — never hardcode) ──────────────
define('CARDSAVE_MERCHANT_ID', getenv('CARDSAVE_MERCHANT_ID') ?: '');
define('CARDSAVE_PASSWORD',    getenv('CARDSAVE_PASSWORD')    ?: '');

// ── AI ────────────────────────────────────────────────────────────
define('ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY') ?: '');
define('ANTHROPIC_MODEL',   'claude-sonnet-4-6');

// ── Session ───────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// ── Helpers ───────────────────────────────────────────────────────
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
