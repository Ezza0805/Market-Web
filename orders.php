<?php
// orders.php - User Purchase History
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

require_login('/orders.php');

$current_u = current_user();
$all_orders = json_db_read('orders');

// Filter orders for the logged-in user (unless admin)
$my_orders = array_filter($all_orders, function($o) use ($current_u) {
    if ($current_u['role'] === 'admin') {
        return true; // Admin can view all
    }
    return isset($o['user_id']) && (int)$o['user_id'] === (int)$current_u['id'];
});

// Sort by date descending
usort($my_orders, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

$page_title = 'My Orders';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="mt-4 mb-4 flex-between">
        <div>
            <h1 class="font-bold" style="font-size: 1.8rem; color: var(--secondary);">Order History</h1>
            <p class="text-muted">
                <?= $current_u['role'] === 'admin' ? 'Displaying all marketplace orders (Admin Mode).' : 'Track and manage your recent purchases.' ?>
            </p>
        </div>
        <a href="/index.php" class="btn btn-primary-sm">Browse Products</a>
    </div>

    <?php if (empty($my_orders)): ?>
        <div class="cart-table-card text-center" style="padding: 60px 20px;">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 14px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
            <h2>No Orders Found</h2>
            <p class="text-muted mt-2">You haven't placed any orders yet. Start exploring our catalog!</p>
            <a href="/index.php" class="btn btn-primary mt-4">Start Shopping</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 50px;">
            <?php foreach ($my_orders as $ord): ?>
                <div class="cart-table-card">
                    <div class="flex-between mb-3" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <span class="font-bold" style="font-size: 1.1rem; color: var(--secondary);"><?= e($ord['order_number']) ?></span>
                            <span class="text-muted" style="font-size: 0.8rem; margin-left: 10px;">Placed on <?= date('M d, Y - H:i', strtotime($ord['created_at'])) ?></span>
                            <?php if ($current_u['role'] === 'admin'): ?>
                                <span class="badge-role user" style="margin-left: 8px;">Customer: <?= e($ord['username']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-status <?= strtolower($ord['status']) ?>"><?= ucfirst($ord['status']) ?></span>
                            <span class="font-bold" style="font-size: 1.1rem; color: var(--primary);"><?= CURRENCY . number_format($ord['total_amount'], 2) ?></span>
                        </div>
                    </div>

                    <!-- Order items list -->
                    <table class="cart-table" style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ord['items'] as $item): ?>
                                <tr>
                                    <td class="font-bold"><?= e($item['name']) ?></td>
                                    <td><?= CURRENCY . number_format($item['price'], 2) ?></td>
                                    <td><?= (int)$item['quantity'] ?></td>
                                    <td class="font-bold"><?= CURRENCY . number_format($item['subtotal'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Shipping and payment info snippet -->
                    <div class="flex-between mt-3 text-muted" style="font-size: 0.825rem; background: #f8fafc; padding: 10px 14px; border-radius: var(--radius-sm);">
                        <div>
                            <strong>Shipping To:</strong> <?= e($ord['shipping_address']['full_name']) ?>, <?= e($ord['shipping_address']['address']) ?>, <?= e($ord['shipping_address']['city']) ?> (<?= e($ord['shipping_address']['phone']) ?>)
                        </div>
                        <div>
                            <strong>Payment:</strong> <?= e($ord['payment_method']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
