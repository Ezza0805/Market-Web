# NovaMarket - Native PHP Flat-File Marketplace

A lightweight, modern, responsive e-commerce web platform engineered natively with **PHP 8.0+**, **JSON Flat-File Database**, **Vanilla CSS**, **Vanilla JavaScript**, and zero external frameworks.

---

## 🌟 Key Features

### 1. 3 Distinct User Roles
* **Guest (Unauthenticated)**:
  - Can browse the entire product catalog, search, filter, and view detailed product specifications.
  - Restricted from adding products to the shopping cart or checking out. If attempted, a modal dialog and toast notification prompt the visitor to sign in or create an account.
* **User (Authenticated Customer)**:
  - Can browse items, add items to cart with live quantity adjustment.
  - Full checkout flow: entering shipping address, choosing payment method, placing orders, and viewing personal order history with status tracking.
* **Admin (Store Manager)**:
  - Access to the dedicated **Admin Dashboard** (`/admin/index.php`).
  - **Live Traffic Monitoring**: tracks every visitor request (IP address, user agent, route, method, role, timestamp).
  - **HTML5 Canvas 24-Hour Traffic Chart**: pure Vanilla JavaScript chart visualizing traffic volume.
  - **Product Inventory Management**: add new products, update prices, manage stock, and delete products.
  - **Order Fulfillment**: review customer orders and update status (`Pending`, `Processing`, `Completed`, `Cancelled`).

### 2. Flat-File JSON Database with File Locking
* Data is stored in human-readable JSON files in `data/`:
  - `data/products.json`: Catalog items, categories, prices, stock, ratings, and vector icons.
  - `data/users.json`: Registered users with hashed passwords and role designations.
  - `data/orders.json`: Placed orders, customer information, line items, and fulfillment statuses.
  - `data/traffic.json`: Web request audit logs for traffic analytics.
* Thread-safe reads and writes using PHP's `flock()` with `LOCK_SH` and `LOCK_EX`.

### 3. Security Architecture
* **Session Cookie Hardening**:
  - `HttpOnly`: Session cookies are inaccessible to JavaScript, protecting against XSS session theft.
  - `SameSite=Lax`: Defends against Cross-Site Request Forgery (CSRF).
  - `session_regenerate_id(true)` upon login to neutralize Session Fixation attacks.
* **Anti-SQL & Code Injection Layer**:
  - `sanitize_input_secure()` automatically detects, flags, and neutralizes common SQL injection patterns (`UNION SELECT`, `' OR 1=1`, `DROP TABLE`, `;--`, comment tags, and null bytes).
* **CSRF Token Protection**:
  - High-entropy cryptographic tokens (`bin2hex(random_bytes(32))`) validated on all mutating forms (add product, cart mutation, checkout, status change).
* **Bcrypt Password Hashing**:
  - Passwords are encrypted using PHP's native `password_hash($pwd, PASSWORD_DEFAULT)`.
* **Output Sanitization (Anti-XSS)**:
  - All dynamic data rendered via `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).

### 4. Pure Vanilla CSS & Vanilla JS
* Zero external CSS frameworks (no Tailwind, no Bootstrap).
* Responsive mobile-friendly grid, sleek cards, interactive modals, toasts, dropdowns, and an HTML5 canvas graph.

---

## 🚀 How to Run Locally

### Prerequisites
* PHP 8.0 or newer installed and available in your system path.

### Start the Server
Open your terminal (PowerShell, Command Prompt, or bash) in this project folder and run:

```bash
php -S localhost:8000
```

Now open your web browser and navigate to:
👉 **[http://localhost:8000](http://localhost:8000)**

---

## 🔑 Demo Accounts

| Role | Username / Email | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` / `admin@marketplace.local` | `Admin123!` | Full control, Traffic Dashboard, Product CRUD, Orders |
| **User** | `customer` / `user@marketplace.local` | `User123!` | Add to cart, Checkout, View personal order history |
| **Guest** | *(Do not log in)* | *(N/A)* | Browse catalog & product details (view-only) |

> 💡 **Tip:** On the [Sign In page](http://localhost:8000/login.php), there are convenient **"Quick Fill"** buttons that will populate the test credentials with a single click.

---

## 📁 Directory Structure

```text
F:\agy demo/
├── admin/
│   ├── index.php          # Admin Dashboard & Real-Time Traffic Monitor
│   ├── orders.php         # Customer orders manager & status updates
│   ├── products.php       # Product catalog CRUD & stock inventory
│   └── traffic.php        # Deep-dive traffic audit log & purge tool
├── assets/
│   ├── css/
│   │   └── style.css      # Vanilla CSS stylesheet
│   └── js/
│       ├── main.js        # Modals, toasts, cart interactivity
│       └── traffic-chart.js # Pure Canvas 24-hr traffic chart engine
├── data/
│   ├── orders.json        # Order storage
│   ├── products.json      # Product catalog storage
│   ├── traffic.json       # Real-time traffic log storage
│   └── users.json         # User credentials & roles
├── includes/
│   ├── auth.php           # Authentication & role-based access control
│   ├── cart.php           # Cart logic with guest restrictions
│   ├── config.php         # Constants, session config & security headers
│   ├── db.php             # Thread-safe JSON database engine
│   ├── footer.php         # Common footer, modals, and toast container
│   ├── header.php         # Navbar, announcement banner & traffic hook
│   ├── security.php       # Anti-SQLi filter, XSS escaping & CSRF protection
│   └── svg_icons.php      # Crisp vector graphics for products & UI
├── cart.php               # Shopping cart page
├── cart_action.php        # Cart API endpoint (AJAX & Form)
├── checkout.php           # Secure checkout workflow
├── index.php              # Marketplace storefront catalog
├── login.php              # Sign in with session cookie security
├── logout.php             # Safe session destruction
├── orders.php             # User purchase history
├── product.php            # Detailed product view
├── register.php           # Customer registration
└── README.md              # Project documentation
```
