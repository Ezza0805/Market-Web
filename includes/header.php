<?php
// includes/header.php - Global Navigation Header
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/tracker.php';

// Record traffic for this request
track_traffic();

$current_role = get_role();
$user = current_user();
$cart_total_qty = cart_count();
$flash = get_flash();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - ' . SITE_NAME : SITE_NAME . ' - ' . SITE_TAGLINE ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body data-role="<?= e($current_role) ?>">
    <!-- Top Announcement Bar -->
    <div class="top-announcement">
        <div class="container announcement-content">
            <div class="announcement-left">
                <span class="pulse-dot"></span>
                <span>Role: <strong><?= ucfirst($current_role) ?></strong></span>
                <?php if ($current_role === 'guest'): ?>
                    <span class="role-desc">&bull; You are currently browsing as a Guest (Read-only mode. Login to add items to cart and buy).</span>
                <?php elseif ($current_role === 'user'): ?>
                    <span class="role-desc">&bull; Full shopping access enabled (Add to cart & Checkout).</span>
                <?php elseif ($current_role === 'admin'): ?>
                    <span class="role-desc">&bull; Administrator privileges active (Traffic monitoring & catalog maintenance).</span>
                <?php endif; ?>
            </div>
            <div class="announcement-right">
                <?php if ($current_role === 'admin'): ?>
                    <a href="/admin/index.php" class="btn-top-admin">⚡ Go to Admin Dashboard</a>
                <?php else: ?>
                    <span>Free Shipping on orders over $100!</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="navbar-wrapper">
        <div class="container navbar-inner">
            <a href="/index.php" class="brand-logo">
                <div class="logo-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="brand-name"><?= SITE_NAME ?></span>
                    <span class="brand-sub">MARKETPLACE</span>
                </div>
            </a>

            <!-- Quick Search Bar -->
            <form action="/index.php" method="GET" class="nav-search-form">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" placeholder="Search gadgets, audio, laptops..." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" class="nav-search-input">
                    <?php if (!empty($_GET['q'])): ?>
                        <a href="/index.php" class="search-clear-btn">&times;</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Navigation Actions -->
            <div class="nav-actions">
                <a href="/index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">Explore</a>

                <!-- Shopping Cart (Visible to all, but behaves differently for guest vs user) -->
                <a href="/cart.php" class="nav-cart-btn <?= $current_page === 'cart.php' ? 'active' : '' ?>" id="cart-nav-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="cart-label">Cart</span>
                    <span class="cart-badge" id="cart-badge-count"><?= $cart_total_qty ?></span>
                </a>

                <div class="nav-divider"></div>

                <!-- Auth State -->
                <?php if ($user): ?>
                    <div class="user-menu-dropdown">
                        <button class="user-pill-btn" id="userMenuToggle">
                            <span class="user-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
                            <span class="user-name"><?= e($user['username']) ?></span>
                            <span class="role-tag <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="dropdown-panel" id="userMenuPanel">
                            <div class="dropdown-header">
                                <strong><?= e($user['full_name']) ?></strong>
                                <small><?= e($user['email']) ?></small>
                            </div>
                            <div class="dropdown-divider"></div>
                            <?php if ($user['role'] === 'admin'): ?>
                                <a href="/admin/index.php" class="dropdown-item admin-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                    Admin Dashboard
                                </a>
                                <a href="/admin/products.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    Manage Products
                                </a>
                                <a href="/admin/traffic.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                                    Live Traffic Monitor
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a href="/orders.php" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                My Orders
                            </a>
                            <a href="/logout.php" class="dropdown-item text-danger">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                Sign Out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-outline-sm">Sign In</a>
                    <a href="/register.php" class="btn btn-primary-sm">Create Account</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Flash Notifications Container -->
    <?php if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible" id="flash-alert">
                <div class="alert-icon">
                    <?php if ($flash['type'] === 'success'): ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php elseif ($flash['type'] === 'warning'): ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <?php else: ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <?php endif; ?>
                </div>
                <div class="alert-text"><?= e($flash['message']) ?></div>
                <button type="button" class="alert-close" onclick="document.getElementById('flash-alert').remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>
