<?php
// includes/config.php - Global configuration and security headers

// Strict error reporting for clean code, disable display in production
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Base Paths
define('ROOT_PATH', dirname(__DIR__));
define('DATA_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'data');
define('SESSION_DIR', DATA_DIR . DIRECTORY_SEPARATOR . 'sessions');
define('SITE_NAME', 'NovaMarket');
define('SITE_TAGLINE', 'Your Modern Native Marketplace');
define('CURRENCY', '$');

// Load security & sanitization utilities globally
require_once __DIR__ . '/security.php';

// Ensure data & session directories exist
if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0755, true);
}
if (!is_dir(SESSION_DIR)) {
    @mkdir(SESSION_DIR, 0755, true);
}

// Secure session cookie settings before session_start
if (session_status() === PHP_SESSION_NONE) {
    if (is_dir(SESSION_DIR)) {
        @session_save_path(SESSION_DIR);
    }

    // Session cookie parameters for security
    $cookieParams = [
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,      // Protect session cookie from JavaScript access (anti-XSS)
        'samesite' => 'Lax'      // CSRF protection for cookies
    ];
    @session_set_cookie_params($cookieParams);
    @session_start();
}

// Flash messaging helpers
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
