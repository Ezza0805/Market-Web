<?php
// register.php - User Registration Page
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: /index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    $username = sanitize_input_secure($_POST['username'] ?? '');
    $email = sanitize_input_secure($_POST['email'] ?? '');
    $full_name = sanitize_input_secure($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        $res = register_user($username, $email, $password, $full_name);
        if ($res['success']) {
            set_flash('success', 'Account created successfully! Welcome to NovaMarket, ' . $username . '.');
            header('Location: /index.php');
            exit;
        } else {
            $error = $res['message'];
        }
    }
}

$page_title = 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title">Create Account</h1>
            <p class="text-muted">Join NovaMarket to add items to cart, checkout, and track orders.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-4">
                <div class="alert-text"><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="/register.php" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g. Alex Johnson" value="<?= e($_POST['full_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="e.g. alexj" pattern="[a-zA-Z0-9_]{3,30}" title="Letters, numbers and underscores only" value="<?= e($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="alex@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password (min 6 characters)</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirm" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                Register as Customer
            </button>
        </form>

        <div class="text-center mt-4" style="font-size: 0.875rem;">
            Already have an account? <a href="/login.php" style="color: var(--primary); font-weight: 700;">Sign in here</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
