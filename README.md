# Olives & Lemon — Cafe Online Ordering System

A full-stack online ordering system for **Olives & Lemon Cafe**, built with HTML, CSS, JavaScript (vanilla) and PHP.

## Quick Start

```bash
# Linux / macOS
./start_store.sh

# Windows
start_store.bat
```

Then open **http://127.0.0.1:3000/** in your browser.

> Requires **PHP 8.0+** installed and available in PATH.

---

## Pages

| Page | URL | Description |
|------|-----|-------------|
| Home (Landing) | `/home.html` | Brand landing page with video header, about section, gallery |
| Shop / Order | `/index.html` | Full product catalog, cart, ordering |
| Checkout | `/checkout.html` | Customer info, payment method, place order |
| Receipt | `/receipt.html?id=...` | Printable receipt |
| Track Order | `/track.html?id=...` | Live order status tracker |
| About | `/about.html` | Company story |
| Location | `/location.html` | Map and contact info |
| Gallery | `/gallery.html` | Photo gallery with filters |
| Services | `/services.html` | Services offered |

## Admin Panel

| Page | URL | Description |
|------|-----|-------------|
| Admin Login | `/admin_panel/` | Auto-redirects to login |
| Dashboard | `/admin_panel/admin.html` | Order management, KPIs, export |
| POS Report | `/admin_panel/pos.html` | End-of-day sales breakdown |

**Default credentials:** `admin` / `admin123`

### Admin Features
- **Dashboard KPIs**: Total orders, completed, preparing, today's sales
- **Order Workflow**: New → Preparing → Ready → Completed
- **Quick Actions**: Refresh, export today's orders as CSV
- **Search & Filter**: By name, phone, order ID, or status
- **POS Reports**: Daily sales by payment method and order type
- **Print & CSV Export**: Print reports or download CSV

### Security
- Session-based authentication with 30-minute timeout
- PHP auth middleware (`auth_check.php`) on all admin pages
- ⚠️ Change the default password in `login.php` before production!

---

## Project Structure

```
oliveslemon/
├── index.html          # Shop / ordering page
├── home.html           # Landing page
├── checkout.html       # Checkout flow
├── receipt.html        # Order receipt
├── track.html          # Order tracking
├── about.html          # About page
├── location.html       # Location & map
├── gallery.html        # Photo gallery
├── services.html       # Services page
├── shop.js             # Product catalog, cart, shop logic
├── checkout.js         # Payment flow, order submission
├── receipt.js          # Receipt rendering
├── track.js            # Order tracking with polling
├── shop.css            # Shop & checkout styles
├── styles.css          # Landing & info page styles
├── orders.php          # Orders REST API (GET/POST/PATCH/DELETE)
├── products.php        # Products API
├── util.php            # PHP helpers (JSON read/write)
├── data/
│   └── orders.json     # Order storage (auto-created)
├── admin_panel/
│   ├── index.php       # Redirect to login
│   ├── login.php       # Admin login page
│   ├── auth_check.php  # Auth middleware
│   ├── logout.php      # Logout handler
│   ├── admin.html      # Admin dashboard
│   ├── admin.js        # Admin logic
│   ├── pos.html        # POS report page
│   └── pos.js          # POS report logic
├── assets/
│   ├── images/         # Product & brand images
│   └── video/          # Landing page video
├── start_store.sh      # Linux/macOS start script
├── start_store.bat     # Windows start script
└── capacitor.config.json # Mobile app config
```

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (vanilla), Bootstrap 5, Font Awesome 6
- **Backend**: PHP (built-in server)
- **Data**: JSON file storage (`data/orders.json`)
- **Cart**: localStorage (`olives-lemon-cart-v1`)
- **Mobile**: Capacitor wrapper (optional)

## API Endpoints

### `orders.php`

| Method | Params | Description |
|--------|--------|-------------|
| `GET` | — | List all orders (newest first) |
| `GET` | `?id=ord_...` | Get single order |
| `POST` | JSON body | Create new order |
| `PATCH` | JSON `{id, status}` | Update order status |
| `DELETE` | `?id=ord_...` | Delete order |

### `products.php`

| Method | Description |
|--------|-------------|
| `GET` | List all products with variants and pricing |

## Payment Methods

- **Cash** — default, no online payment required
- **PayPal** — sandbox integration
- **GCash** — simulated payment flow
- **Card** — simulated payment flow

## Coupon Codes

| Code | Discount |
|------|----------|
| `PROMO10` | ₱10 off |
| `PROMO20` | ₱20 off |

## Contact

- **Location**: Salvilla St., San Miguel, Philippines
- **Hours**: 9:00am — 8:00pm, Monday — Sunday
- **Phone**: (+63) 933 855 2489
