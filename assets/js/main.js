// assets/js/main.js - Vanilla JavaScript Interactions

document.addEventListener('DOMContentLoaded', () => {
    // 1. User dropdown menu toggle
    const userToggleBtn = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userMenuPanel');

    if (userToggleBtn && userDropdown) {
        userToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!userDropdown.contains(e.target) && !userToggleBtn.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    }

    // 2. Intercept "Add to Cart" and Cart interactions for Guests
    const userRole = document.body.dataset.role || 'guest';

    document.querySelectorAll('.js-add-to-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // GUEST RESTRICTION: Block adding to cart
            if (userRole === 'guest') {
                openGuestModal();
                showToast('Action Restricted', 'Please log in as a User to add items to your cart.', 'warning');
                return;
            }

            // Normal user add to cart via AJAX
            const productId = btn.dataset.productId;
            const qtyInput = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
            const quantity = qtyInput ? qtyInput.value : 1;

            addToCartAjax(productId, quantity, btn);
        });
    });

    // 3. Cart button in nav for guest: if cart is empty or guest clicks, guide them
    const cartNavBtn = document.getElementById('cart-nav-btn');
    if (cartNavBtn && userRole === 'guest') {
        cartNavBtn.addEventListener('click', (e) => {
            const badge = document.getElementById('cart-badge-count');
            if (badge && parseInt(badge.textContent.trim()) === 0) {
                // If cart is empty, show prompt
                // Let navigation proceed or trigger modal if preferred
            }
        });
    }

    // 4. Quantity Steppers
    document.querySelectorAll('.qty-stepper').forEach(stepper => {
        const minusBtn = stepper.querySelector('.qty-minus');
        const plusBtn = stepper.querySelector('.qty-plus');
        const input = stepper.querySelector('.qty-input');

        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', () => {
                let current = parseInt(input.value) || 1;
                if (current > 1) {
                    input.value = current - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });

            plusBtn.addEventListener('click', () => {
                let current = parseInt(input.value) || 1;
                let max = parseInt(input.getAttribute('max')) || 999;
                if (current < max) {
                    input.value = current + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
});

/**
 * Perform Ajax Add to Cart
 */
function addToCartAjax(productId, quantity, triggerBtn) {
    const originalText = triggerBtn.innerHTML;
    triggerBtn.disabled = true;
    triggerBtn.innerHTML = '<span>Adding...</span>';

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/cart_action.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        triggerBtn.disabled = false;
        triggerBtn.innerHTML = originalText;

        if (data.success) {
            // Update cart badge
            const badge = document.getElementById('cart-badge-count');
            if (badge) {
                badge.textContent = data.cart_count;
                badge.classList.remove('bounce');
                void badge.offsetWidth; // trigger reflow
                badge.classList.add('bounce');
            }
            showToast('Success!', data.message, 'success');
        } else {
            if (data.role_blocked) {
                openGuestModal();
            }
            showToast('Notice', data.message || 'Could not add to cart.', 'error');
        }
    })
    .catch(err => {
        triggerBtn.disabled = false;
        triggerBtn.innerHTML = originalText;
        showToast('Error', 'An error occurred while communicating with the server.', 'error');
    });
}

/**
 * Open & Close Guest Modal
 */
function openGuestModal() {
    const modal = document.getElementById('guestModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeGuestModal() {
    const modal = document.getElementById('guestModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Toast Notification Function
 */
function showToast(title, message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    let iconSvg = '';
    if (type === 'success') {
        iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    } else if (type === 'error') {
        iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    } else if (type === 'warning') {
        iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
    } else {
        iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
    }

    toast.innerHTML = `
        <div class="toast-icon">${iconSvg}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button type="button" class="alert-close" style="font-size:1rem;">&times;</button>
    `;

    toast.querySelector('.alert-close').addEventListener('click', () => {
        toast.remove();
    });

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(60px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Quick fill helper for demo login
function fillDemo(username, password) {
    const userInput = document.querySelector('input[name="identifier"]');
    const passInput = document.querySelector('input[name="password"]');
    if (userInput && passInput) {
        userInput.value = username;
        passInput.value = password;
        showToast('Demo Credentials Filled', `Loaded credentials for: ${username}`, 'info');
    }
}
