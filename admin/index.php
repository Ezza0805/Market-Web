<?php
// admin/index.php - Admin Dashboard & Real-Time Traffic Analytics
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/tracker.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/svg_icons.php';

// Route Guard: Admin only!
require_admin();

$analytics = get_traffic_analytics();
$products = json_db_read('products');
$orders = json_db_read('orders');

// Calculate total revenue
$total_revenue = 0.0;
foreach ($orders as $ord) {
    if (($ord['status'] ?? '') !== 'cancelled') {
        $total_revenue += (float)($ord['total_amount'] ?? 0);
    }
}

// Low stock items
$low_stock_count = 0;
foreach ($products as $p) {
    if ($p['stock'] <= 5) $low_stock_count++;
}

// Prepare hourly chart data for JS
$hours_labels = [];
$hours_data = [];
for ($h = 0; $h < 24; $h++) {
    $hours_labels[] = sprintf('%02d:00', $h);
    $hours_data[] = $analytics['hourly_today'][$h] ?? 0;
}

$page_title = 'Admin Traffic & Performance Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div style="padding: 0 10px 20px; font-weight: 800; color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
            Admin Panel
        </div>
        <ul class="admin-sidebar-nav">
            <li class="admin-nav-item active">
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
                    Orders (<?= count($orders) ?>)
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="/admin/traffic.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    Full Traffic Logs
                </a>
            </li>
            <li class="admin-nav-item" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                <a href="/index.php" target="_blank">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    View Storefront &rarr;
                </a>
            </li>
        </ul>
    </aside>

    <!-- Admin Main Body -->
    <main class="admin-main">
        <div class="admin-header-row">
            <div>
                <h1 class="admin-title">Marketplace Traffic & Control Center</h1>
                <p class="text-muted">Live monitoring of user visits, system health, and sales performance.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="/admin/products.php?action=add" class="btn btn-primary">+ Add New Product</a>
                <button type="button" class="btn btn-outline" onclick="location.reload()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    Refresh Stats
                </button>
            </div>
        </div>

        <!-- KPI Metric Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: #3b82f6;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
                <div class="kpi-info">
                    <small>Total Pageviews</small>
                    <h3><?= number_format($analytics['total_visits']) ?></h3>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: #10b981;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div class="kpi-info">
                    <small>Today's Unique IPs</small>
                    <h3><?= number_format($analytics['unique_today']) ?></h3>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: #8b5cf6;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <div class="kpi-info">
                    <small>Total Revenue</small>
                    <h3><?= CURRENCY . number_format($total_revenue, 2) ?></h3>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: #f59e0b;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div class="kpi-info">
                    <small>Active Products</small>
                    <h3><?= count($products) ?> <?= $low_stock_count > 0 ? '<span style="font-size:0.75rem; color:#ef4444;">(' . $low_stock_count . ' low stock)</span>' : '' ?></h3>
                </div>
            </div>
        </div>

        <!-- Real-Time Traffic Canvas Chart -->
        <div class="chart-container-card">
            <div class="chart-header">
                <div>
                    <h3 class="font-bold" style="color: var(--secondary);">Today's Traffic Activity (24-Hour Timeline)</h3>
                    <p class="text-muted" style="font-size: 0.85rem;">Rendered dynamically via HTML5 Canvas with native PHP telemetry.</p>
                </div>
                <span class="badge-role user font-bold">Today: <?= $analytics['today_visits'] ?> hits</span>
            </div>
            <div class="canvas-wrapper">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>

        <!-- Two Columns: Role Breakdown & Top Visited Pages -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
            <!-- Role Distribution Breakdown -->
            <div class="data-table-card">
                <h3 class="font-bold mb-3" style="font-size: 1.15rem; color: var(--secondary);">Traffic by Role Breakdown</h3>
                <p class="text-muted mb-4" style="font-size: 0.825rem;">Observe how Guests convert to logged-in Users.</p>
                
                <?php
                $tot = max(1, $analytics['total_visits']);
                $guest_pct = round(($analytics['role_breakdown']['guest'] / $tot) * 100);
                $user_pct = round(($analytics['role_breakdown']['user'] / $tot) * 100);
                $admin_pct = round(($analytics['role_breakdown']['admin'] / $tot) * 100);
                ?>

                <div style="margin-bottom: 16px;">
                    <div class="flex-between mb-1" style="font-size: 0.85rem;">
                        <span>👤 <strong>Guest Visitors</strong> (Read-only)</span>
                        <strong><?= $analytics['role_breakdown']['guest'] ?> (<?= $guest_pct ?>%)</strong>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: <?= $guest_pct ?>%; height: 100%; background: #64748b;"></div>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <div class="flex-between mb-1" style="font-size: 0.85rem;">
                        <span>🛒 <strong>Logged-in Users</strong> (Shoppers)</span>
                        <strong><?= $analytics['role_breakdown']['user'] ?> (<?= $user_pct ?>%)</strong>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: <?= $user_pct ?>%; height: 100%; background: #2563eb;"></div>
                    </div>
                </div>

                <div>
                    <div class="flex-between mb-1" style="font-size: 0.85rem;">
                        <span>⚡ <strong>Administrators</strong></span>
                        <strong><?= $analytics['role_breakdown']['admin'] ?> (<?= $admin_pct ?>%)</strong>
                    </div>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: <?= $admin_pct ?>%; height: 100%; background: #ef4444;"></div>
                    </div>
                </div>
            </div>

            <!-- Top Visited Pages -->
            <div class="data-table-card">
                <h3 class="font-bold mb-3" style="font-size: 1.15rem; color: var(--secondary);">Most Popular Pages</h3>
                <p class="text-muted mb-3" style="font-size: 0.825rem;">Pages generating the most customer interest.</p>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Page Route</th>
                            <th class="text-right">Hits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($analytics['top_pages'])): ?>
                            <tr><td colspan="2" class="text-center text-muted">No hits recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($analytics['top_pages'] as $page_path => $hits): ?>
                                <tr>
                                    <td><code><?= e($page_path) ?></code></td>
                                    <td class="text-right font-bold"><?= number_format($hits) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Live Traffic Stream Table -->
        <div class="data-table-card">
            <div class="flex-between mb-3">
                <div>
                    <h3 class="font-bold" style="font-size: 1.15rem; color: var(--secondary);">Live Traffic Activity Feed</h3>
                    <p class="text-muted" style="font-size: 0.825rem;">Real-time visitor request log with IP, role, and route.</p>
                </div>
                <a href="/admin/traffic.php" class="btn btn-outline-sm">View All Logs &rarr;</a>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>IP Address</th>
                        <th>Role</th>
                        <th>User</th>
                        <th>Method</th>
                        <th>Route</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($analytics['recent_logs'])): ?>
                        <tr><td colspan="6" class="text-center text-muted">No traffic data recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($analytics['recent_logs'] as $log): ?>
                            <tr>
                                <td class="text-muted" style="font-size: 0.775rem; white-space: nowrap;">
                                    <?= date('H:i:s - M d', strtotime($log['datetime'])) ?>
                                </td>
                                <td><code><?= e($log['ip']) ?></code></td>
                                <td>
                                    <span class="badge-role <?= e($log['role']) ?>">
                                        <?= e($log['role']) ?>
                                    </span>
                                </td>
                                <td><strong><?= e($log['username']) ?></strong></td>
                                <td><span style="font-size:0.75rem; font-weight:700; background:#f1f5f9; padding:2px 6px; border-radius:4px;"><?= e($log['method']) ?></span></td>
                                <td><span style="color:var(--primary); font-family:monospace;"><?= e($log['path']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Chart initialization script -->
<script src="/assets/js/traffic-chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hoursLabels = <?= json_encode($hours_labels) ?>;
    const hoursData = <?= json_encode($hours_data) ?>;
    renderTrafficChart('trafficChart', hoursLabels, hoursData, 'Hits');

    // Re-render chart on window resize for responsiveness
    window.addEventListener('resize', () => {
        renderTrafficChart('trafficChart', hoursLabels, hoursData, 'Hits');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
