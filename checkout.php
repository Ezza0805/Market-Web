<?php
// checkout.php - Secure Checkout Workflow
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/svg_icons.php';

// ENFORCE USER ROLE: Guests cannot checkout!
require_login('/checkout.php');

$cart_data = cart_get_details();
if (empty($cart_data['items'])) {
    set_flash('info', 'Your cart is empty. Please add products before checking out.');
    header('Location: /index.php');
    exit;
}

$errors = [];
$current_u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    // Sanitize shipping details with anti-injection filter
    $full_name = sanitize_input_secure($_POST['full_name'] ?? '');
    $address = sanitize_input_secure($_POST['address'] ?? '');
    $city = sanitize_input_secure($_POST['city'] ?? '');
    $postal_code = sanitize_input_secure($_POST['postal_code'] ?? '');
    $phone = sanitize_input_secure($_POST['phone'] ?? '');
    $payment_method = sanitize_input_secure($_POST['payment_method'] ?? 'Credit Card');

    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($address)) $errors[] = 'Shipping address is required.';
    if (empty($city)) $errors[] = 'City is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';

    if (empty($errors)) {
        // Verify current stock availability before writing order
        $can_fulfill = true;
        $products_db = json_db_read('products');

        foreach ($cart_data['items'] as $item) {
            foreach ($products_db as $p) {
                if ($p['id'] == $item['id']) {
                    if ($p['stock'] < $item['quantity']) {
                        $errors[] = 'Insufficient stock for "' . $p['name'] . '". Only ' . $p['stock'] . ' available.';
                        $can_fulfill = false;
                    }
                    break;
                }
            }
        }

        if ($can_fulfill) {
            // 1. Create order record in orders.json
            $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $order = [
                'order_number' => $order_number,
                'user_id' => (int)$current_u['id'],
                'username' => $current_u['username'],
                'items' => array_map(function($i) {
                    return [
                        'id' => $i['id'],
                        'name' => $i['name'],
                        'price' => $i['price'],
                        'quantity' => $i['quantity'],
                        'subtotal' => $i['subtotal']
                    ];
                }, $cart_data['items']),
                'subtotal' => $cart_data['subtotal'],
                'shipping' => $cart_data['shipping'],
                'tax' => $cart_data['tax'],
                'total_amount' => $cart_data['total'],
                'shipping_address' => [
                    'full_name' => $full_name,
                    'address' => $address,
                    'city' => $city,
                    'postal_code' => $postal_code,
                    'phone' => $phone
                ],
                'payment_method' => $payment_method,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $saved_order = json_db_insert('orders', $order);

            // 2. Reduce stock & increase sales_count in products.json
            foreach ($cart_data['items'] as $item) {
                $p = json_db_find_by_id('products', $item['id']);
                if ($p) {
                    $new_stock = max(0, $p['stock'] - $item['quantity']);
                    $new_sales = ($p['sales_count'] ?? 0) + $item['quantity'];
                    json_db_update('products', $item['id'], [
                        'stock' => $new_stock,
                        'sales_count' => $new_sales
                    ]);
                }
            }

            // 3. Clear cart
            cart_clear();

            set_flash('success', 'Order #' . $order_number . ' placed successfully! Thank you for your purchase.');
            header('Location: /orders.php?placed=1');
            exit;
        }
    }
}

$page_title = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="mt-4 mb-4">
        <h1 class="font-bold" style="font-size: 1.8rem; color: var(--secondary);">Secure Checkout</h1>
        <p class="text-muted">Review your items and enter shipping destination.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-4">
            <ul style="margin-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="cart-layout">
        <!-- Shipping Details Form -->
        <div class="cart-table-card">
            <h3 class="font-bold mb-4" style="font-size: 1.25rem;">1. Shipping Information</h3>
            <form action="/checkout.php" method="POST" id="checkoutForm">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Full Recipient Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($_POST['full_name'] ?? $current_u['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="address" class="form-control" placeholder="123 Example St, Apt 4B" value="<?= e($_POST['address'] ?? '') ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" placeholder="San Francisco" value="<?= e($_POST['city'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postal / Zip Code</label>
                        <input type="text" name="postal_code" class="form-control" placeholder="94107" value="<?= e($_POST['postal_code'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000" value="<?= e($_POST['phone'] ?? '') ?>" required>
                </div>

                <h3 class="font-bold mt-4 mb-3" style="font-size: 1.25rem;">2. Payment Method</h3>
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; background: #ffffff;">
                        <input type="radio" name="payment_method" value="Credit Card" checked>
                        <span>💳 <strong>Credit / Debit Card</strong> (Instant Online Simulation)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; background: #ffffff;">
                        <input type="radio" name="payment_method" value="Bank Transfer">
                        <span>🏦 <strong>Bank Wire Transfer</strong> (Manual Verification)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; background: #ffffff;">
                        <input type="radio" name="payment_method" value="Cash on Delivery">
                        <span>💵 <strong>Cash on Delivery (COD)</strong></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Confirm & Place Order (<?= CURRENCY . number_format($cart_data['total'], 2) ?>)
                </button>
            </form>
        </div>

        <!-- Order Items Overview -->
        <div class="order-summary-card">
            <h3 class="font-bold mb-3" style="font-size: 1.25rem;">Items in Order (<?= count($cart_data['items']) ?>)</h3>
            <div style="max-height: 280px; overflow-y: auto; margin-bottom: 20px; padding-right: 6px;">
                <?php foreach ($cart_data['items'] as $item): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="cart-thumb" style="width: 36px; height: 36px; background: <?= e($item['image_color']) ?>15;">
                                <?= get_product_svg($item['icon'], $item['image_color'], 24, 24) ?>
                            </div>
                            <div>
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--secondary);"><?= e($item['name']) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">Qty: <?= $item['quantity'] ?> &times; <?= CURRENCY . number_format($item['price'], 2) ?></div>
                            </div>
                        </div>
                        <div class="font-bold" style="font-size: 0.9rem;"><?= CURRENCY . number_format($item['subtotal'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-line">
                <span class="text-muted">Subtotal</span>
                <span class="font-bold"><?= CURRENCY . number_format($cart_data['subtotal'], 2) ?></span>
            </div>
            <div class="summary-line">
                <span class="text-muted">Shipping</span>
                <span class="font-bold"><?= $cart_data['shipping'] == 0 ? '<span class="text-success">FREE</span>' : CURRENCY . number_format($cart_data['shipping'], 2) ?></span>
            </div>
            <div class="summary-line">
                <span class="text-muted">Tax (5%)</span>
                <span class="font-bold"><?= CURRENCY . number_format($cart_data['tax'], 2) ?></span>
            </div>
            <div class="summary-line total">
                <span>Total Due</span>
                <span style="color: var(--primary);"><?= CURRENCY . number_format($cart_data['total'], 2) ?></span>
            </div>

            <div class="security-card-snippet mt-4">
                <div class="security-item">
                    <span class="sec-dot"></span>
                    <span>256-bit Encrypted Transaction</span>
                </div>
                <div class="security-item">
                    <span class="sec-dot"></span>
                    <span>Direct Native JSON Order Confirmation</span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
