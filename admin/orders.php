<?php
// admin/orders.php - Admin Order Fulfillment & Status Manager
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_admin();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $order_id = sanitize_int($_POST['order_id'] ?? 0);
    $new_status = sanitize_string($_POST['status'] ?? '');

    $allowed = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed) && $order_id > 0) {
        json_db_update('orders', $order_id, ['status' => $new_status]);
        set_flash('success', 'Order status updated to "' . ucfirst($new_status) . '".');
    }
    header('Location: /admin/orders.php');
    exit;
}

$orders = json_db_read('orders');
usort($orders, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

$page_title = 'Manage Orders - Admin';
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
            <li class="admin-nav-item active">
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
        </ul>
    </aside>

    <main class="admin-main">
        <div class="admin-header-row">
            <div>
                <h1 class="admin-title">Customer Orders Management</h1>
                <p class="text-muted">Review, process, and update shipping fulfillment status.</p>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="data-table-card text-center" style="padding: 60px 20px;">
                <h3>No Orders Found</h3>
                <p class="text-muted mt-2">When customers place orders, they will appear here.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($orders as $ord): ?>
                    <div class="data-table-card">
                        <div class="flex-between mb-3" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <span class="font-bold" style="font-size: 1.15rem; color: var(--secondary);"><?= e($ord['order_number']) ?></span>
                                <span class="text-muted" style="font-size: 0.8rem; margin-left: 10px;">Placed on <?= date('Y-m-d H:i', strtotime($ord['created_at'])) ?></span>
                                <span class="badge-role user" style="margin-left: 8px;">Customer: <?= e($ord['username']) ?></span>
                            </div>
                            
                            <!-- Status Update Form -->
                            <form action="/admin/orders.php" method="POST" style="display:flex; align-items:center; gap: 8px;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                <span class="badge-status <?= strtolower($ord['status']) ?>"><?= ucfirst($ord['status']) ?></span>
                                <select name="status" class="sort-select" style="padding: 4px 8px; font-size: 0.8rem;" onchange="this.form.submit()">
                                    <option value="pending" <?= $ord['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $ord['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="completed" <?= $ord['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $ord['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </div>

                        <!-- Items Breakdown -->
                        <table class="admin-table" style="font-size: 0.875rem;">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ord['items'] as $item): ?>
                                    <tr>
                                        <td><strong><?= e($item['name']) ?></strong></td>
                                        <td><?= CURRENCY . number_format($item['price'], 2) ?></td>
                                        <td><?= (int)$item['quantity'] ?></td>
                                        <td class="text-right font-bold"><?= CURRENCY . number_format($item['subtotal'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="flex-between mt-3" style="background:#f8fafc; padding:12px; border-radius: var(--radius-sm); font-size: 0.85rem;">
                            <div>
                                <strong>Shipping Address:</strong> 
                                <?= e($ord['shipping_address']['full_name']) ?> &bull; 
                                <?= e($ord['shipping_address']['address']) ?>, <?= e($ord['shipping_address']['city']) ?> <?= e($ord['shipping_address']['postal_code']) ?> &bull; 
                                Phone: <?= e($ord['shipping_address']['phone']) ?>
                            </div>
                            <div class="font-bold" style="font-size: 1.1rem; color: var(--primary);">
                                Total: <?= CURRENCY . number_format($ord['total_amount'], 2) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
