<?php
// login.php - Secure User Login Page
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: /index.php');
    exit;
}

$error = '';
$redirect_to = sanitize_string($_GET['redirect'] ?? '/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    // Sanitize with anti-SQL injection layer
    $identifier = sanitize_input_secure($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = sanitize_string($_POST['redirect'] ?? '/index.php');

    $res = attempt_login($identifier, $password);
    if ($res['success']) {
        set_flash('success', 'Welcome back, ' . $res['user']['username'] . '!');
        
        // If user is admin, they might prefer the dashboard
        if ($res['user']['role'] === 'admin' && $redirect === '/index.php') {
            header('Location: /admin/index.php');
        } else {
            header('Location: ' . (filter_var($redirect, FILTER_SANITIZE_URL) ?: '/index.php'));
        }
        exit;
    } else {
        $error = $res['message'];
    }
}

$page_title = 'Sign In';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title">Welcome Back</h1>
            <p class="text-muted">Sign in to your account to add products to cart and checkout.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-4">
                <div class="alert-text"><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="/login.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= e($redirect_to) ?>">

            <div class="form-group">
                <label class="form-label">Username or Email</label>
                <input type="text" name="identifier" class="form-control" placeholder="admin or customer" value="<?= e($_POST['identifier'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                Sign In
            </button>
        </form>

        <!-- Quick Demo Credentials Box for Testing -->
        <div class="demo-credentials-box">
            <div class="font-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase;">⚡ Quick Demo Accounts</div>
            <div class="demo-cred-row">
                <div>
                    <strong>User (Customer):</strong> <span class="text-muted">customer / User123!</span>
                </div>
                <button type="button" class="btn-quick-fill" onclick="fillDemo('customer', 'User123!')">Fill</button>
            </div>
            <div class="demo-cred-row">
                <div>
                    <strong>Admin:</strong> <span class="text-muted">admin / Admin123!</span>
                </div>
                <button type="button" class="btn-quick-fill" onclick="fillDemo('admin', 'Admin123!')">Fill</button>
            </div>
        </div>

        <div class="text-center mt-4" style="font-size: 0.875rem;">
            Don't have an account? <a href="/register.php" style="color: var(--primary); font-weight: 700;">Create one</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
