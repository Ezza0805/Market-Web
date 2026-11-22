<?php
// cart.php - Shopping Cart View
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/svg_icons.php';

$page_title = 'Your Shopping Cart';
require_once __DIR__ . '/includes/header.php';

$cart_data = cart_get_details();
$items = $cart_data['items'];
$free_shipping_threshold = 100.0;
$remaining_for_free_shipping = max(0, $free_shipping_threshold - $cart_data['subtotal']);
$progress_percent = min(100, round(($cart_data['subtotal'] / $free_shipping_threshold) * 100));
?>

<main class="container">
    <div class="mt-4 mb-3 flex-between">
        <h1 class="font-bold" style="font-size: 1.8rem; color: var(--secondary);">Shopping Cart</h1>
        <a href="/index.php" class="btn btn-outline-sm">&larr; Continue Shopping</a>
    </div>

    <!-- Notice for Guest visiting Cart -->
    <?php if ($current_role === 'guest'): ?>
        <div class="alert alert-warning mb-4">
            <div class="alert-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <div class="alert-text">
                <strong>Guest Browsing Notice:</strong> As a Guest, you can inspect the cart overview, but only registered <strong>Users</strong> can add items or complete purchases. 
                <a href="/login.php?redirect=/cart.php" style="color:var(--primary); font-weight:700; text-decoration:underline;">Click here to sign in</a>.
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="cart-table-card text-center" style="padding: 60px 20px;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 16px;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            <h2>Your Cart is Empty</h2>
            <p class="text-muted mt-2">Looks like you haven't added any products to your cart yet.</p>
            <a href="/index.php" class="btn btn-primary mt-4">Discover Products</a>
        </div>
    <?php else: ?>
        <!-- Free Shipping Progress Tracker -->
        <div class="cart-table-card mb-4" style="background: #ffffff; padding: 18px 24px;">
            <div class="flex-between mb-2" style="font-size: 0.85rem;">
                <?php if ($remaining_for_free_shipping > 0): ?>
                    <span>Add <strong><?= CURRENCY . number_format($remaining_for_free_shipping, 2) ?></strong> more for <strong>FREE Shipping!</strong></span>
                <?php else: ?>
                    <span class="text-success font-bold">🎉 Congratulations! You have unlocked FREE Shipping!</span>
                <?php endif; ?>
                <span class="font-bold text-muted"><?= $progress_percent ?>%</span>
            </div>
            <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div style="width: <?= $progress_percent ?>%; height: 100%; background: linear-gradient(90deg, #3b82f6, #10b981); transition: width 0.4s ease;"></div>
            </div>
        </div>

        <div class="cart-layout">
            <!-- Items Table -->
            <div class="cart-table-card">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="cart-item-info">
                                        <div class="cart-thumb" style="background: <?= e($item['image_color']) ?>15; border: 1px solid <?= e($item['image_color']) ?>40;">
                                            <?= get_product_svg($item['icon'], $item['image_color'], 38, 38) ?>
                                        </div>
                                        <div>
                                            <a href="/product.php?id=<?= $item['id'] ?>" class="cart-item-name"><?= e($item['name']) ?></a>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?= e($item['category']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-bold"><?= CURRENCY . number_format($item['price'], 2) ?></td>
                                <td>
                                    <form action="/cart_action.php" method="POST" style="display:inline-flex; align-items:center;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <div class="qty-stepper" style="border-radius:4px;">
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="qty-input" style="width: 44px; height: 32px;" onchange="this.form.submit()">
                                        </div>
                                    </form>
                                </td>
                                <td class="font-bold" style="color: var(--secondary);"><?= CURRENCY . number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <form action="/cart_action.php" method="POST" onsubmit="return confirm('Remove this item from your cart?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-ghost" style="padding: 6px; color: var(--danger);" title="Remove">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="flex-between mt-4">
                    <form action="/cart_action.php" method="POST" onsubmit="return confirm('Empty entire cart?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-sm text-danger">Clear Cart</button>
                    </form>
                    <span class="text-muted" style="font-size: 0.85rem;">Stock verified with instant reservation</span>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary-card">
                <h3 class="font-bold mb-4" style="font-size: 1.25rem;">Order Summary</h3>
                
                <div class="summary-line">
                    <span class="text-muted">Subtotal (<?= $cart_data['count'] ?> items)</span>
                    <span class="font-bold"><?= CURRENCY . number_format($cart_data['subtotal'], 2) ?></span>
                </div>
                <div class="summary-line">
                    <span class="text-muted">Estimated Shipping</span>
                    <span class="font-bold"><?= $cart_data['shipping'] == 0 ? '<span class="text-success">FREE</span>' : CURRENCY . number_format($cart_data['shipping'], 2) ?></span>
                </div>
                <div class="summary-line">
                    <span class="text-muted">Estimated Tax (5%)</span>
                    <span class="font-bold"><?= CURRENCY . number_format($cart_data['tax'], 2) ?></span>
                </div>

                <div class="summary-line total">
                    <span>Total Amount</span>
                    <span style="color: var(--primary);"><?= CURRENCY . number_format($cart_data['total'], 2) ?></span>
                </div>

                <!-- Checkout Button: Enforce Role -->
                <?php if ($current_role === 'guest'): ?>
                    <button type="button" class="btn btn-primary btn-block btn-lg mt-4" onclick="openGuestModal()">
                        Sign In to Checkout
                    </button>
                    <p class="text-center text-muted mt-2" style="font-size: 0.8rem;">
                        Guests must create an account or sign in to complete a purchase.
                    </p>
                <?php else: ?>
                    <a href="/checkout.php" class="btn btn-primary btn-block btn-lg mt-4">
                        Proceed to Checkout &rarr;
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
