<?php
// includes/cart.php - Shopping Cart Management

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Initialize Cart in session
 */
function cart_init() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Get raw cart items [product_id => quantity]
 */
function cart_get_raw() {
    cart_init();
    return $_SESSION['cart'];
}

/**
 * Get total quantity count of items in cart
 */
function cart_count() {
    cart_init();
    $total = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $total += (int)$qty;
    }
    return $total;
}

/**
 * Add product to cart
 * ROLE CHECK: GUEST CANNOT ADD TO CART!
 */
function cart_add($product_id, $quantity = 1) {
    cart_init();
    $product_id = (int)$product_id;
    $quantity = max(1, (int)$quantity);

    // ROLE ENFORCEMENT: Only logged-in Users can add to cart
    if (is_guest()) {
        return [
            'success' => false,
            'role_blocked' => true,
            'message' => 'Guests cannot add items to cart. Please log in or register to shop!'
        ];
    }

    $product = json_db_find_by_id('products', $product_id);
    if (!$product) {
        return ['success' => false, 'message' => 'Product not found.'];
    }

    $current_in_cart = $_SESSION['cart'][$product_id] ?? 0;
    $new_qty = $current_in_cart + $quantity;

    if ($new_qty > $product['stock']) {
        return [
            'success' => false,
            'message' => 'Cannot add more. Only ' . $product['stock'] . ' available in stock.'
        ];
    }

    $_SESSION['cart'][$product_id] = $new_qty;
    return [
        'success' => true,
        'message' => 'Added "' . $product['name'] . '" to your cart!',
        'cart_count' => cart_count()
    ];
}

/**
 * Update quantity in cart
 */
function cart_update($product_id, $quantity) {
    cart_init();
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    if (is_guest()) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
        return ['success' => true, 'message' => 'Item removed from cart.'];
    }

    $product = json_db_find_by_id('products', $product_id);
    if (!$product) {
        unset($_SESSION['cart'][$product_id]);
        return ['success' => false, 'message' => 'Product no longer exists.'];
    }

    if ($quantity > $product['stock']) {
        $_SESSION['cart'][$product_id] = $product['stock'];
        return [
            'success' => false,
            'message' => 'Quantity adjusted to maximum available stock (' . $product['stock'] . ').'
        ];
    }

    $_SESSION['cart'][$product_id] = $quantity;
    return ['success' => true, 'message' => 'Cart updated.'];
}

/**
 * Remove an item from cart
 */
function cart_remove($product_id) {
    cart_init();
    $product_id = (int)$product_id;
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    return ['success' => true, 'message' => 'Item removed from cart.'];
}

/**
 * Clear whole cart
 */
function cart_clear() {
    $_SESSION['cart'] = [];
}

/**
 * Detailed Cart calculation with product metadata
 */
function cart_get_details() {
    cart_init();
    $items = [];
    $subtotal = 0.0;

    foreach ($_SESSION['cart'] as $p_id => $qty) {
        $product = json_db_find_by_id('products', $p_id);
        if ($product) {
            $line_total = (float)$product['price'] * (int)$qty;
            $subtotal += $line_total;
            $items[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'category' => $product['category'],
                'price' => (float)$product['price'],
                'stock' => (int)$product['stock'],
                'quantity' => (int)$qty,
                'subtotal' => $line_total,
                'image_color' => $product['image_color'] ?? '#3b82f6',
                'icon' => $product['icon'] ?? 'box'
            ];
        } else {
            // Product removed from DB
            unset($_SESSION['cart'][$p_id]);
        }
    }

    $shipping = $subtotal > 0 ? ($subtotal >= 100 ? 0.0 : 9.99) : 0.0;
    $tax = round($subtotal * 0.05, 2); // 5% standard tax
    $total = $subtotal + $shipping + $tax;

    return [
        'items' => $items,
        'count' => count($items),
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ];
}
