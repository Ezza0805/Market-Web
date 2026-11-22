<?php
// admin/traffic.php - Dedicated Full Traffic Monitor & Audit Log
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/tracker.php';

require_admin();

// Purge traffic logs if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'purge') {
    require_csrf_token();
    json_db_write('traffic', []);
    set_flash('info', 'Traffic audit logs have been successfully cleared.');
    header('Location: /admin/traffic.php');
    exit;
}

$all_traffic = json_db_read('traffic');

// Filter by Role
$filter_role = sanitize_string($_GET['role'] ?? '');
if (!empty($filter_role)) {
    $all_traffic = array_filter($all_traffic, function($t) use ($filter_role) {
        return ($t['role'] ?? 'guest') === $filter_role;
    });
}

// Search by IP or Path
$search = sanitize_input_secure($_GET['q'] ?? '');
if (!empty($search)) {
    $all_traffic = array_filter($all_traffic, function($t) use ($search) {
        return stripos($t['ip'] ?? '', $search) !== false || 
               stripos($t['path'] ?? '', $search) !== false || 
               stripos($t['username'] ?? '', $search) !== false;
    });
}

$all_traffic = array_reverse($all_traffic); // latest first

$page_title = 'Live Traffic Monitor - Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div style="padding: 0 10px 20px; font-weight: 800; color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
            Admin Panel
        </div>
        <ul class="admin-sidebar-nav">
            <li class="admin-nav-item">
                <a href="/admin/index.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard & Traffic
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="/admin/products.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Manage Products
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="/admin/orders.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    Orders
                </a>
            </li>
            <li class="admin-nav-item active">
                <a href="/admin/traffic.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    Full Traffic Logs
                </a>
            </li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="admin-header-row">
            <div>
                <h1 class="admin-title">Real-Time Traffic Audit Inspector</h1>
                <p class="text-muted">Total recorded requests: <?= count($all_traffic) ?></p>
            </div>
            
            <form action="/admin/traffic.php" method="POST" onsubmit="return confirm('Purge all traffic records? This action cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="purge">
                <button type="submit" class="btn btn-outline text-danger">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Purge Traffic Logs
                </button>
            </form>
        </div>

        <!-- Filter bar -->
        <div class="data-table-card mb-4" style="padding: 16px 20px;">
            <form action="/admin/traffic.php" method="GET" style="display:flex; gap:14px; align-items:center; flex-wrap:wrap;">
                <input type="text" name="q" class="form-control" placeholder="Search IP, URL, or username..." value="<?= e($search) ?>" style="max-width: 320px;">
                
                <select name="role" class="sort-select" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="guest" <?= $filter_role === 'guest' ? 'selected' : '' ?>>Guest</option>
                    <option value="user" <?= $filter_role === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $filter_role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>

                <button type="submit" class="btn btn-primary-sm">Filter</button>
                <?php if (!empty($search) || !empty($filter_role)): ?>
                    <a href="/admin/traffic.php" class="btn btn-ghost" style="font-size:0.85rem;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Traffic Logs Table -->
        <div class="data-table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Timestamp</th>
                        <th>IP Address</th>
                        <th>Role</th>
                        <th>Identified As</th>
                        <th>Method</th>
                        <th>Requested Path</th>
                        <th>Browser / User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_traffic)): ?>
                        <tr><td colspan="8" class="text-center text-muted" style="padding: 40px;">No traffic records match your query.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($all_traffic, 0, 100) as $idx => $t): ?>
                            <tr>
                                <td><?= $t['id'] ?? ($idx + 1) ?></td>
                                <td style="white-space:nowrap; font-size:0.775rem;" class="text-muted"><?= e($t['datetime']) ?></td>
                                <td><code><?= e($t['ip']) ?></code></td>
                                <td>
                                    <span class="badge-role <?= e($t['role'] ?? 'guest') ?>">
                                        <?= e($t['role'] ?? 'guest') ?>
                                    </span>
                                </td>
                                <td><strong><?= e($t['username'] ?? 'Guest') ?></strong></td>
                                <td><span style="font-size:0.75rem; font-weight:700; background:#f1f5f9; padding:2px 6px; border-radius:4px;"><?= e($t['method']) ?></span></td>
                                <td><span style="color:var(--primary); font-family:monospace;"><?= e($t['path']) ?></span></td>
                                <td class="text-muted" style="font-size:0.75rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= e($t['user_agent'] ?? '') ?>">
                                    <?= e($t['user_agent'] ?? '') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
