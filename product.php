<?php
// product.php - Product Detail View
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/svg_icons.php';

$product_id = sanitize_int($_GET['id'] ?? 0);
$product = json_db_find_by_id('products', $product_id);

if (!$product) {
    set_flash('error', 'Product not found.');
    header('Location: /index.php');
    exit;
}

$page_title = $product['name'];
require_once __DIR__ . '/includes/header.php';

// Related products
$all_products = json_db_read('products');
$related = array_filter($all_products, function($p) use ($product) {
    return $p['category'] === $product['category'] && $p['id'] !== $product['id'];
});
$related = array_slice($related, 0, 3);
?>

<main class="container">
    <!-- Breadcrumb -->
    <div class="mt-4 mb-2 text-muted" style="font-size: 0.85rem;">
        <a href="/index.php" style="color: inherit; text-decoration: none;">Marketplace</a> &rsaquo; 
        <a href="/index.php?category=<?= urlencode($product['category']) ?>" style="color: inherit; text-decoration: none;"><?= e($product['category']) ?></a> &rsaquo; 
        <span style="color: var(--text-main); font-weight: 600;"><?= e($product['name']) ?></span>
    </div>

    <!-- Product Layout -->
    <div class="product-detail-layout">
        <!-- Visual Display -->
        <div class="detail-image-box">
            <?= get_product_svg($product['icon'] ?? 'laptop', $product['image_color'] ?? '#2563eb', 240, 240) ?>
        </div>

        <!-- Details & Actions -->
        <div class="detail-content">
            <div class="flex-between">
                <span class="product-category" style="font-size: 0.85rem;"><?= e($product['category']) ?></span>
                <span class="product-rating">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <?= number_format($product['rating'], 1) ?> (<?= $product['sales_count'] ?> sales)
                </span>
            </div>

            <h1 class="hero-title" style="font-size: 2rem; margin-top: 8px; color: var(--secondary);"><?= e($product['name']) ?></h1>

            <div class="detail-price">
                <?= CURRENCY . number_format($product['price'], 2) ?>
            </div>

            <p class="text-muted" style="line-height: 1.7;"><?= e($product['description']) ?></p>

            <ul class="detail-meta-list">
                <li><strong>Stock Status:</strong> 
                    <?php if ($product['stock'] > 0): ?>
                        <span class="text-success"><?= $product['stock'] ?> units in stock</span>
                    <?php else: ?>
                        <span class="text-danger">Out of stock</span>
                    <?php endif; ?>
                </li>
                <li><strong>Shipping:</strong> Standard 2-3 business days &bull; Free over $100</li>
                <li><strong>Warranty:</strong> 1-Year Official Manufacturer Warranty</li>
            </ul>

            <!-- Quantity & Add to Cart -->
            <?php if ($product['stock'] > 0): ?>
                <div class="flex-between mt-4" style="gap: 16px; flex-wrap: wrap;">
                    <div class="qty-stepper">
                        <button type="button" class="qty-btn qty-minus">-</button>
                        <input type="number" class="qty-input" data-product-id="<?= $product['id'] ?>" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>

                    <button type="button" 
                            class="btn btn-primary btn-lg js-add-to-cart <?= $current_role === 'guest' ? 'guest-trigger' : '' ?>" 
                            data-product-id="<?= $product['id'] ?>"
                            style="flex: 1;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <?= $current_role === 'guest' ? 'Add to Cart (Sign In Required)' : 'Add to Cart' ?>
                    </button>
                </div>

                <?php if ($current_role === 'guest'): ?>
                    <p class="mt-3 text-muted" style="font-size: 0.8rem; background: #f1f5f9; padding: 8px 12px; border-radius: 6px;">
                        ℹ️ <strong>Guest Mode:</strong> You can view product specs and pricing freely. To place an order, please <a href="/login.php" style="color:var(--primary); font-weight:700;">Sign In</a> or <a href="/register.php" style="color:var(--primary); font-weight:700;">Register</a>.
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning mt-4">This product is currently out of stock. Please check back later!</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
        <section class="mt-5 mb-5">
            <h3 class="mb-3 font-bold" style="font-size: 1.3rem;">Related Products</h3>
            <div class="products-grid">
                <?php foreach ($related as $rel): ?>
                    <div class="product-card">
                        <div class="product-image-container" style="height: 160px;">
                            <div class="product-svg-wrapper">
                                <?= get_product_svg($rel['icon'] ?? 'laptop', $rel['image_color'] ?? '#2563eb', 100, 100) ?>
                            </div>
                        </div>
                        <div class="product-body">
                            <span class="product-category"><?= e($rel['category']) ?></span>
                            <h4 class="product-title" style="font-size: 0.95rem;">
                                <a href="/product.php?id=<?= $rel['id'] ?>"><?= e($rel['name']) ?></a>
                            </h4>
                            <div class="product-footer">
                                <span class="product-price" style="font-size: 1.15rem;"><?= CURRENCY . number_format($rel['price'], 2) ?></span>
                                <button type="button" class="btn-add-cart js-add-to-cart <?= $current_role === 'guest' ? 'guest-trigger' : '' ?>" data-product-id="<?= $rel['id'] ?>">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
