<?php
// admin/products.php - Manage Product Inventory Catalog
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/svg_icons.php';

require_admin();

$action = sanitize_string($_GET['action'] ?? 'list');
$product_id = sanitize_int($_GET['id'] ?? 0);
$errors = [];

// Handle POST actions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $post_action = sanitize_string($_POST['action'] ?? '');

    if ($post_action === 'save') {
        $id = sanitize_int($_POST['product_id'] ?? 0);
        $name = sanitize_input_secure($_POST['name'] ?? '');
        $category = sanitize_input_secure($_POST['category'] ?? '');
        $price = sanitize_float($_POST['price'] ?? 0);
        $stock = sanitize_int($_POST['stock'] ?? 0);
        $badge = sanitize_input_secure($_POST['badge'] ?? '');
        $icon = sanitize_string($_POST['icon'] ?? 'laptop');
        $color = sanitize_string($_POST['image_color'] ?? '#2563eb');
        $desc = sanitize_input_secure($_POST['description'] ?? '');

        if (empty($name)) $errors[] = 'Product name is required.';
        if (empty($category)) $errors[] = 'Category is required.';
        if ($price <= 0) $errors[] = 'Valid price is required.';

        if (empty($errors)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
            $product_data = [
                'name' => $name,
                'slug' => $slug,
                'category' => $category,
                'price' => $price,
                'stock' => $stock,
                'badge' => $badge,
                'icon' => $icon,
                'image_color' => $color,
                'description' => $desc,
                'rating' => 5.0,
                'sales_count' => 0
            ];

            if ($id > 0) {
                // Keep previous rating and sales_count
                $existing = json_db_find_by_id('products', $id);
                if ($existing) {
                    $product_data['rating'] = $existing['rating'] ?? 5.0;
                    $product_data['sales_count'] = $existing['sales_count'] ?? 0;
                }
                json_db_update('products', $id, $product_data);
                set_flash('success', 'Product "' . $name . '" updated successfully.');
            } else {
                json_db_insert('products', $product_data);
                set_flash('success', 'New product "' . $name . '" added to catalog.');
            }

            header('Location: /admin/products.php');
            exit;
        }
    } elseif ($post_action === 'delete') {
        $id = sanitize_int($_POST['product_id'] ?? 0);
        if ($id > 0) {
            json_db_delete('products', $id);
            set_flash('info', 'Product has been deleted from inventory.');
        }
        header('Location: /admin/products.php');
        exit;
    }
}

$products = json_db_read('products');
$editing_product = ($action === 'edit' && $product_id > 0) ? json_db_find_by_id('products', $product_id) : null;

$page_title = 'Manage Products - Admin';
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
            <li class="admin-nav-item active">
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
                <h1 class="admin-title">Product Inventory Catalog</h1>
                <p class="text-muted">Maintain product listings, prices, stock levels, and illustrations.</p>
            </div>
            <?php if ($action === 'list'): ?>
                <a href="/admin/products.php?action=add" class="btn btn-primary">+ Add New Product</a>
            <?php else: ?>
                <a href="/admin/products.php" class="btn btn-outline">&larr; Back to Products List</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error mb-4">
                <ul style="margin-left: 20px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= e($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Product Form (Add / Edit) -->
            <div class="data-table-card" style="max-width: 720px;">
                <h3 class="font-bold mb-4"><?= $action === 'edit' ? 'Edit Product: ' . e($editing_product['name']) : 'Add New Marketplace Product' ?></h3>
                
                <form action="/admin/products.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="product_id" value="<?= $editing_product['id'] ?? 0 ?>">

                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" value="<?= e($editing_product['name'] ?? '') ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="Electronics, Audio, Gadgets..." value="<?= e($editing_product['category'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Corner Badge (Optional)</label>
                            <input type="text" name="badge" class="form-control" placeholder="Best Seller, Hot, New, Popular" value="<?= e($editing_product['badge'] ?? '') ?>">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Price (USD $)</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?= e($editing_product['price'] ?? '99.99') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" value="<?= e($editing_product['stock'] ?? '10') ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Vector Icon</label>
                            <select name="icon" class="form-control">
                                <?php 
                                $icons = ['laptop', 'headphones', 'mouse', 'keyboard', 'bag', 'monitor', 'watch', 'coffee'];
                                foreach ($icons as $ic):
                                ?>
                                    <option value="<?= $ic ?>" <?= ($editing_product['icon'] ?? '') === $ic ? 'selected' : '' ?>><?= ucfirst($ic) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Accent Theme Color</label>
                            <input type="color" name="image_color" class="form-control" style="height: 44px; padding: 4px;" value="<?= e($editing_product['image_color'] ?? '#2563eb') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?= e($editing_product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="flex-between mt-4">
                        <a href="/admin/products.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Products Table List -->
            <div class="data-table-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Sales</th>
                            <th>Rating</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="cart-thumb" style="width: 40px; height: 40px; background: <?= e($p['image_color'] ?? '#2563eb') ?>15;">
                                            <?= get_product_svg($p['icon'] ?? 'laptop', $p['image_color'] ?? '#2563eb', 28, 28) ?>
                                        </div>
                                        <div>
                                            <a href="/product.php?id=<?= $p['id'] ?>" target="_blank" class="font-bold" style="color:var(--secondary); text-decoration:none;">
                                                <?= e($p['name']) ?>
                                            </a>
                                            <?php if (!empty($p['badge'])): ?>
                                                <span style="font-size: 0.7rem; background:#fee2e2; color:#b91c1c; padding:2px 6px; border-radius:10px; margin-left:4px;">
                                                    <?= e($p['badge']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="product-category" style="font-size:0.75rem;"><?= e($p['category']) ?></span></td>
                                <td class="font-bold"><?= CURRENCY . number_format($p['price'], 2) ?></td>
                                <td>
                                    <?php if ($p['stock'] <= 5): ?>
                                        <span class="text-danger font-bold">⚠️ <?= $p['stock'] ?> (Low)</span>
                                    <?php else: ?>
                                        <span class="text-success font-bold"><?= $p['stock'] ?> in stock</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $p['sales_count'] ?? 0 ?></td>
                                <td>⭐ <?= number_format($p['rating'] ?? 5.0, 1) ?></td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <a href="/admin/products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-outline-sm" style="padding: 4px 10px; font-size: 0.8rem;">Edit</a>
                                    
                                    <form action="/admin/products.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this product permanently?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-ghost" style="padding: 4px 8px; color: var(--danger); font-size: 0.8rem;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
