<?php
// includes/footer.php - Global Footer & Modal Templates
?>
    <!-- Footer Section -->
    <footer class="footer-wrapper">
        <div class="container footer-inner">
            <div class="footer-col brand-col">
                <div class="brand-logo footer-logo">
                    <div class="logo-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <span class="brand-name"><?= SITE_NAME ?></span>
                </div>
                <p class="footer-desc">A native PHP marketplace powered by lightweight JSON flat-file storage, session-secured authentication, vanilla CSS styling, and real-time traffic monitoring.</p>
                <div class="tech-stack-badges">
                    <span class="badge-tech">PHP 8.0+ Native</span>
                    <span class="badge-tech">JSON Database</span>
                    <span class="badge-tech">Vanilla CSS3</span>
                    <span class="badge-tech">Zero Frameworks</span>
                </div>
            </div>

            <div class="footer-col">
                <h4>Marketplace</h4>
                <ul class="footer-links">
                    <li><a href="/index.php">All Products</a></li>
                    <li><a href="/index.php?category=Electronics">Electronics</a></li>
                    <li><a href="/index.php?category=Audio">Audio Gear</a></li>
                    <li><a href="/index.php?category=Accessories">Accessories</a></li>
                    <li><a href="/cart.php">Shopping Cart</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>User Roles & Access</h4>
                <ul class="footer-links">
                    <li><span class="role-hint"><strong>Guest:</strong> Browse products (view only)</span></li>
                    <li><span class="role-hint"><strong>User:</strong> Cart, Checkout, Order History</span></li>
                    <li><span class="role-hint"><strong>Admin:</strong> Traffic analytics & product maintenance</span></li>
                    <li><a href="/login.php">Sign In / Switch Role</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Security & Architecture</h4>
                <div class="security-card-snippet">
                    <div class="security-item">
                        <span class="sec-dot"></span>
                        <span>Session Cookies with <code>HttpOnly</code> & <code>SameSite</code></span>
                    </div>
                    <div class="security-item">
                        <span class="sec-dot"></span>
                        <span>Anti-SQLi & XSS Input Sanitization</span>
                    </div>
                    <div class="security-item">
                        <span class="sec-dot"></span>
                        <span>CSRF Token Protected Forms</span>
                    </div>
                    <div class="security-item">
                        <span class="sec-dot"></span>
                        <span>Bcrypt Password Hashing</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> Native PHP Marketplace. Built with pure PHP, Vanilla CSS & Vanilla JavaScript.</p>
                <div class="footer-bottom-links">
                    <a href="/admin/index.php">Admin Portal</a>
                    <span>&bull;</span>
                    <a href="#top">Back to top &uarr;</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Guest Restriction Modal (shown when a Guest attempts to add to cart or checkout) -->
    <div class="modal-overlay" id="guestModal" style="display: none;">
        <div class="modal-card">
            <button class="modal-close" onclick="closeGuestModal()">&times;</button>
            <div class="modal-icon-badge">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h3 class="modal-title">Authentication Required</h3>
            <p class="modal-text">You are currently browsing as a <strong>Guest</strong>. Guests can preview products, but you must be logged in as a <strong>User</strong> to add items to your cart or make a purchase.</p>
            
            <div class="demo-quick-login">
                <div class="quick-login-label">💡 Quick Test Credentials:</div>
                <div class="quick-login-row">
                    <span>User: <code>customer</code> / <code>User123!</code></span>
                    <span>Admin: <code>admin</code> / <code>Admin123!</code></span>
                </div>
            </div>

            <div class="modal-actions">
                <a href="/login.php?redirect=/cart.php" class="btn btn-primary btn-block">Sign In Now</a>
                <a href="/register.php" class="btn btn-outline btn-block">Create Free Account</a>
                <button type="button" class="btn btn-ghost btn-block" onclick="closeGuestModal()">Continue Browsing as Guest</button>
            </div>
        </div>
    </div>

    <!-- Interactive Toast Notification Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Core Vanilla JavaScript -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
