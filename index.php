<?php
// index.php - Marketplace Storefront Catalog
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/svg_icons.php';

$page_title = 'Explore Products';
require_once __DIR__ . '/includes/header.php';

// Fetch products from JSON DB
$products = json_db_read('products');

// Get categories list
$categories = array_unique(array_column($products, 'category'));
sort($categories);

// Filter by search query
$search_query = sanitize_input_secure($_GET['q'] ?? '');
if (!empty($search_query)) {
    $products = array_filter($products, function($p) use ($search_query) {
        return stripos($p['name'], $search_query) !== false || 
               stripos($p['description'], $search_query) !== false ||
               stripos($p['category'], $search_query) !== false;
    });
}

// Filter by Category
$selected_category = sanitize_input_secure($_GET['category'] ?? '');
if (!empty($selected_category)) {
    $products = array_filter($products, function($p) use ($selected_category) {
        return strcasecmp($p['category'], $selected_category) === 0;
    });
}

// Sorting
$sort = sanitize_string($_GET['sort'] ?? 'popular');
if ($sort === 'price_asc') {
    usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sort === 'price_desc') {
    usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
} elseif ($sort === 'rating') {
    usort($products, fn($a, $b) => $b['rating'] <=> $a['rating']);
}
?>

<main class="container">
    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="container hero-grid">
            <div class="hero-left">
                <span class="hero-tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    Native PHP 8 &bull; Flat JSON Database
                </span>
                <h1 class="hero-title">Experience Pure Performance with <span class="hero-highlight">NovaMarket</span></h1>
                <p class="hero-subtitle">A modern, responsive e-commerce web platform engineered natively with PHP, JSON persistence, session cookies, anti-SQL injection sanitization, and live traffic analytics.</p>
                
                <div class="hero-stats">
                    <div class="hero-stat-item">
                        <strong><?= count(json_db_read('products')) ?>+</strong>
                        <span>Curated Items</span>
                    </div>
                    <div class="hero-stat-item">
                        <strong>3 Roles</strong>
                        <span>Guest / User / Admin</span>
                    </div>
                    <div class="hero-stat-item">
                        <strong>0%</strong>
                        <span>External Frameworks</span>
                    </div>
                </div>
            </div>

            <!-- Role Access Card Preview -->
            <div class="hero-cards-preview">
                <div class="role-info-card">
                    <div class="role-badge-icon guest-icon">G</div>
                    <div class="role-card-text">
                        <h4>Guest Role (Current)</h4>
                        <p>Browse catalog & details. Cart and checkout are locked until you sign in.</p>
                    </div>
                </div>
                <div class="role-info-card">
                    <div class="role-badge-icon user-icon">U</div>
                    <div class="role-card-text">
                        <h4>User Role (Customer)</h4>
                        <p>Add to cart, manage quantities, checkout orders, and track purchase history.</p>
                    </div>
                </div>
                <div class="role-info-card">
                    <div class="role-badge-icon admin-icon">A</div>
                    <div class="role-card-text">
                        <h4>Admin Role (Manager)</h4>
                        <p>Live traffic monitor dashboard, manage inventory catalog, and fulfill orders.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter & Sort Bar -->
    <section class="filter-bar">
        <div class="category-pills">
            <a href="/index.php" class="category-pill <?= empty($selected_category) ? 'active' : '' ?>">All Categories</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/index.php?category=<?= urlencode($cat) ?>" class="category-pill <?= strcasecmp($selected_category, $cat) === 0 ? 'active' : '' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form action="/index.php" method="GET" class="sort-form">
            <?php if (!empty($selected_category)): ?>
                <input type="hidden" name="category" value="<?= e($selected_category) ?>">
            <?php endif; ?>
            <?php if (!empty($search_query)): ?>
                <input type="hidden" name="q" value="<?= e($search_query) ?>">
            <?php endif; ?>
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Sort: Popularity</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
            </select>
        </form>
    </section>

    <!-- Search feedback if active -->
    <?php if (!empty($search_query)): ?>
        <div class="mb-4">
            <p class="text-muted">Search results for "<strong><?= e($search_query) ?></strong>" (<?= count($products) ?> found) - <a href="/index.php">Clear filter</a></p>
        </div>
    <?php endif; ?>

    <!-- Product Grid -->
    <section class="products-grid">
        <?php if (empty($products)): ?>
            <div class="cart-table-card text-center" style="grid-column: 1 / -1; padding: 60px 20px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 12px;"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                <h3>No products found</h3>
                <p class="text-muted mt-2">Try adjusting your search query or category filter.</p>
                <a href="/index.php" class="btn btn-outline-sm mt-4">Reset Filters</a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <!-- Corner Badge -->
                    <?php if (!empty($product['badge'])): ?>
                        <span class="product-badge-corner <?= strtolower(str_replace(' ', '-', $product['badge'])) ?>">
                            <?= e($product['badge']) ?>
                        </span>
                    <?php endif; ?>

                    <!-- SVG Illustration -->
                    <div class="product-image-container">
                        <div class="product-svg-wrapper">
                            <?= get_product_svg($product['icon'] ?? 'laptop', $product['image_color'] ?? '#2563eb', 130, 130) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="product-body">
                        <div class="product-meta">
                            <span class="product-category"><?= e($product['category']) ?></span>
                            <span class="product-rating">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <?= number_format($product['rating'], 1) ?>
                            </span>
                        </div>

                        <h3 class="product-title">
                            <a href="/product.php?id=<?= $product['id'] ?>"><?= e($product['name']) ?></a>
                        </h3>

                        <p class="product-description-snippet"><?= e($product['description']) ?></p>

                        <div class="product-footer">
                            <div class="product-price-box">
                                <span class="product-price"><?= CURRENCY . number_format($product['price'], 2) ?></span>
                                <span class="product-stock-tag <?= $product['stock'] <= 5 ? 'low-stock' : '' ?>">
                                    <?= $product['stock'] > 0 ? ($product['stock'] <= 5 ? 'Only ' . $product['stock'] . ' left!' : 'In Stock (' . $product['stock'] . ')') : 'Out of stock' ?>
                                </span>
                            </div>

                            <!-- Button with Role Protection -->
                            <?php if ($product['stock'] > 0): ?>
                                <button type="button" 
                                        class="btn-add-cart js-add-to-cart <?= $current_role === 'guest' ? 'guest-trigger' : '' ?>" 
                                        data-product-id="<?= $product['id'] ?>"
                                        title="<?= $current_role === 'guest' ? 'Login required to add to cart' : 'Add to Cart' ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                                    <?= $current_role === 'guest' ? 'Add (Login)' : 'Add' ?>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-sm" disabled style="opacity: 0.6; cursor: not-allowed;">Sold Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
