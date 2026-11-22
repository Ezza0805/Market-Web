<?php
// cart_action.php - Handles AJAX and Form Cart Manipulations
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart.php';

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// GUEST CHECK: A Guest cannot perform cart mutations!
if (is_guest()) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'role_blocked' => true,
            'message' => 'Guests cannot add to cart. Please log in as a User.'
        ]);
        exit;
    } else {
        set_flash('warning', 'You must log in to add items to your cart.');
        header('Location: /login.php');
        exit;
    }
}

$action = sanitize_string($_POST['action'] ?? $_GET['action'] ?? '');
$product_id = sanitize_int($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$quantity = sanitize_int($_POST['quantity'] ?? 1);

$response = ['success' => false, 'message' => 'Invalid action'];

if ($action === 'add') {
    $response = cart_add($product_id, $quantity);
} elseif ($action === 'update') {
    $response = cart_update($product_id, $quantity);
} elseif ($action === 'remove') {
    $response = cart_remove($product_id);
} elseif ($action === 'clear') {
    cart_clear();
    $response = ['success' => true, 'message' => 'Cart cleared.'];
}

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    set_flash($response['success'] ? 'success' : 'error', $response['message']);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/cart.php'));
    exit;
}
