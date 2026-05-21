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
├── database/
│   ├── schema.sql      # Database schema (SQLite)
│   ├── db.php          # PDO connection helper
│   └── migrate.php     # Migration & seed script
├── start_store.sh      # Linux/macOS start script
├── start_store.bat     # Windows start script
└── capacitor.config.json # Mobile app config
```

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (vanilla), Bootstrap 5, Font Awesome 6
- **Backend**: PHP (built-in server)
- **Database**: SQLite via PDO (`database/olives_lemon.db`)
- **Cart**: localStorage (`olives-lemon-cart-v1`)
- **Mobile**: Capacitor wrapper (optional)

## Database Setup

The system uses **SQLite** — no external database server required.

```bash
# Run the migration to create the database, seed products & admin user
php database/migrate.php
```

This creates `database/olives_lemon.db` with:
- **admin_users** — admin credentials (hashed passwords)
- **orders** — order records
- **order_items** — items per order
- **order_item_extras** — extras per item
- **products** — product catalog (76 products, 13 categories)
- **product_variants** — sizes/variants per product
- **product_extras** — available extras per category

Existing orders in `data/orders.json` are automatically migrated.

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

## Deployment

### Requirements
- **PHP 8.0+** with `pdo_sqlite` and `sqlite3` extensions
- **Apache** with `mod_rewrite`, `mod_headers`, `mod_expires`, `mod_deflate` enabled
- OR **Nginx** (see config below)
- Write permissions on the `database/` directory

### Option 1: Shared Hosting (cPanel, Hostinger, etc.)

1. Upload all files to your `public_html` directory (or a subdirectory)
2. The `.htaccess` file handles Apache configuration automatically
3. Ensure PHP 8.0+ is selected in your hosting panel
4. Make the `database/` directory writable: `chmod 755 database/`
5. Visit your domain — the database is created automatically on first access
6. Log in to admin at `yourdomain.com/admin_panel/` and **change the default password**

### Option 2: VPS / Dedicated Server (Apache)

```bash
# Clone the repo
git clone https://github.com/Kokoh20/oliveslemon.git /var/www/oliveslemon

# Set permissions
chown -R www-data:www-data /var/www/oliveslemon
chmod 755 /var/www/oliveslemon/database

# Enable required Apache modules
sudo a2enmod rewrite headers expires deflate
sudo systemctl restart apache2
```

Add a virtual host:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/oliveslemon

    <Directory /var/www/oliveslemon>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Option 3: VPS / Dedicated Server (Nginx)

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/oliveslemon;
    index index.html;

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block database access
    location ~ /database/ { deny all; }
    location ~ /data/ { deny all; }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|webp|gif|css|js|mp4)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Security headers
    add_header X-Content-Type-Options "nosniff";
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
}
```

### Post-Deployment Checklist

- [ ] **Change admin password** — log in at `/admin_panel/` with `admin`/`admin123`, then update
- [ ] **Enable HTTPS** — uncomment the HTTPS redirect in `.htaccess` or configure SSL in Nginx
- [ ] **Verify database directory** is writable by the web server
- [ ] **Test an order** — add item to cart, checkout, verify it appears in admin dashboard
- [ ] **Backup** — set up regular backups of `database/olives_lemon.db`

### Option 4: Local Development

```bash
# Linux / macOS
./start_store.sh

# Windows
start_store.bat

# Or manually
php -S 0.0.0.0:3000
```

## Contact

- **Location**: Salvilla St., San Miguel, Philippines
- **Hours**: 9:00am — 8:00pm, Monday — Sunday
- **Phone**: (+63) 933 855 2489
