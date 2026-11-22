<?php
// includes/auth.php - Authentication & Role-Based Access Control

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user']) && is_array($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Get the currently authenticated user or null
 */
function current_user() {
    return is_logged_in() ? $_SESSION['user'] : null;
}

/**
 * Returns role: 'guest', 'user', or 'admin'
 */
function get_role() {
    if (!is_logged_in()) {
        return 'guest';
    }
    return $_SESSION['user']['role'] ?? 'user';
}

function is_admin() {
    return get_role() === 'admin';
}

function is_customer() {
    return get_role() === 'user';
}

function is_guest() {
    return get_role() === 'guest';
}

/**
 * Authenticate user with username/email and password
 */
function attempt_login($identifier, $password) {
    $identifier = trim($identifier);
    if (empty($identifier) || empty($password)) {
        return ['success' => false, 'message' => 'Please provide both username/email and password.'];
    }

    $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    $user = json_db_find_one('users', function($u) use ($identifier, $is_email) {
        if ($is_email) {
            return strtolower($u['email'] ?? '') === strtolower($identifier);
        }
        return strtolower($u['username'] ?? '') === strtolower($identifier);
    });

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid username/email or password.'];
    }

    // Regenerate session ID to prevent Session Fixation attacks
    session_regenerate_id(true);

    // Save user info in session (excluding password hash)
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'full_name' => $user['full_name'] ?? $user['username']
    ];

    return ['success' => true, 'user' => $_SESSION['user']];
}

/**
 * Register a new customer
 */
function register_user($username, $email, $password, $full_name) {
    $username = trim($username);
    $email = sanitize_email($email);
    $full_name = trim($full_name);

    if (empty($username) || strlen($username) < 3) {
        return ['success' => false, 'message' => 'Username must be at least 3 characters.'];
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores.'];
    }

    if (!$email) {
        return ['success' => false, 'message' => 'Please provide a valid email address.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    // Check uniqueness
    $exists = json_db_find_one('users', function($u) use ($username, $email) {
        return strtolower($u['username']) === strtolower($username) || strtolower($u['email']) === strtolower($email);
    });

    if ($exists) {
        return ['success' => false, 'message' => 'Username or email already in use.'];
    }

    $new_user = [
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user', // Default role for registrations is always 'user'
        'full_name' => $full_name ?: $username,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $created = json_db_insert('users', $new_user);
    if ($created) {
        // Auto login
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$created['id'],
            'username' => $created['username'],
            'email' => $created['email'],
            'role' => $created['role'],
            'full_name' => $created['full_name']
        ];
        return ['success' => true, 'user' => $_SESSION['user']];
    }

    return ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

/**
 * Log out user safely
 */
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Route protection: requires user to be logged in
 */
function require_login($redirect_to = null) {
    if (!is_logged_in()) {
        $target = $redirect_to ?: $_SERVER['REQUEST_URI'];
        set_flash('warning', 'Please log in to access this page.');
        header('Location: /login.php?redirect=' . urlencode($target));
        exit;
    }
}

/**
 * Route protection: requires admin role
 */
function require_admin() {
    if (!is_logged_in()) {
        set_flash('warning', 'Admin authentication required.');
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if (!is_admin()) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title><style>body{font-family:sans-serif;padding:40px;text-align:center;background:#f8fafc;color:#1e293b;}a{color:#2563eb;text-decoration:none;font-weight:bold;}</style></head><body>';
        echo '<h1>403 Forbidden</h1>';
        echo '<p>You do not have administrative privileges to access this area.</p>';
        echo '<p><a href="/index.php">&larr; Return to Marketplace</a></p>';
        echo '</body></html>';
        exit;
    }
}
