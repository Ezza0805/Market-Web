<?php
// includes/security.php - Input Sanitization, SQL Injection Defense & CSRF Protection

/**
 * XSS Clean Escape shorthand for safe HTML output
 */
function e($string) {
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * General string sanitization
 * Removes dangerous tags, null bytes, and trims whitespace
 */
function sanitize_string($input) {
    if (is_null($input)) return '';
    $input = str_replace(chr(0), '', $input); // Null byte removal
    $input = trim($input);
    $input = strip_tags($input);
    return $input;
}

/**
 * SQL Injection Detection & Neutralization Filter
 * Even though we store data in JSON, this security layer screens all incoming inputs
 * against common SQL Injection attack vectors (such as UNION SELECT, OR 1=1, DROP TABLE, '--', etc.)
 * and sanitizes them safely.
 */
function sanitize_input_secure($input) {
    if (is_array($input)) {
        return array_map('sanitize_input_secure', $input);
    }
    
    $clean = sanitize_string($input);

    // List of typical SQL injection attack signatures to neutralize
    $sql_patterns = [
        '/(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE)\b\s+)/i',
        '/(\b(OR|AND)\b\s+[\'"\d\w]+\s*=\s*[\'"\d\w]+)/i',
        '/(--|\#|\/\*|\*\/)/',
        '/(\bEXEC(\s|\+)+(SP_|XP_))/i',
        '/(\bBENCHMARK\b|\bSLEEP\b|\bWAITFOR\b)/i'
    ];

    // If an attack signature is detected, neutralize quotes and dangerous syntax
    foreach ($sql_patterns as $pattern) {
        if (preg_match($pattern, $clean)) {
            // Log security warning
            error_log('[SECURITY WARNING] Potential SQL/Code injection attempt neutralized: ' . substr($clean, 0, 100));
            // Sanitize quotes and comment delimiters
            $clean = str_replace(["'", '"', ';', '--', '/*', '*/'], '', $clean);
        }
    }

    return $clean;
}

/**
 * Email Sanitization and Validation
 */
function sanitize_email($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : false;
}

/**
 * Integer Sanitization
 */
function sanitize_int($value, $min = null, $max = null) {
    $val = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    $val = (int)$val;
    if ($min !== null && $val < $min) $val = $min;
    if ($max !== null && $val > $max) $val = $max;
    return $val;
}

/**
 * Float / Decimal Sanitization (for prices)
 */
function sanitize_float($value, $min = 0.0) {
    $val = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $val = (float)$val;
    if ($min !== null && $val < $min) $val = $min;
    return round($val, 2);
}

/**
 * CSRF Protection Functions
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf_token() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verify_csrf_token($token)) {
        http_response_code(403);
        die('<h3>403 Forbidden: Invalid or expired CSRF token.</h3><p><a href="javascript:history.back()">Go Back</a></p>');
    }
}
