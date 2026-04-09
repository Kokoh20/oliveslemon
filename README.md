# Olives & Lemon Admin Panel

## Access
- **Main URL**: `http://127.0.0.1:3000/admin_panel/` (auto-redirects to login)
- **Direct Login**: `http://127.0.0.1:3000/admin_panel/login.php`
- **Admin Dashboard**: `http://127.0.0.1:3000/admin_panel/admin.html` (requires login)
- **POS System**: `http://127.0.0.1:3000/admin_panel/pos.html` (requires login)
- **Default Login**: `admin` / `admin123`

## Access Flow
1. Go to `http://127.0.0.1:3000/admin_panel/`
2. Automatically redirected to login page
3. Enter credentials
4. Redirected to admin dashboard
5. Access POS and other admin features

## Features

### 📊 **Admin Dashboard**
- **Real-time Metrics**: Total orders, completed, preparing, today's sales
- **Quick Actions**: Direct POS access, refresh dashboard, export orders
- **Order Management**: View, filter, search, update status, delete orders
- **Export Functionality**: Download today's orders as CSV

### 💰 **Integrated POS System**
- **Daily Sales Summary**: Complete breakdown by date
- **Payment Method Analysis**: Cash, GCash, PayPal breakdowns
- **Order Type Tracking**: Pickup vs delivery statistics
- **Detailed Order List**: Individual order information
- **Print & Export**: Print reports, download CSV data

### 🔗 **Seamless Navigation**
- **Admin → POS**: One-click access from dashboard
- **POS → Admin**: Quick return navigation
- **Unified Authentication**: Single login for all admin features
- **Consistent UI**: Matching design and user experience

## Security Features
- Session-based authentication
- 30-minute session timeout
- Automatic logout
- Protected admin pages

## Files Structure
- `login.php` - Login page
- `admin.html` - Main admin dashboard (PHP-protected)
- `auth_check.php` - Authentication middleware
- `logout.php` - Logout handler
- `admin.js` - Admin functionality
- `admin_orders.html` - Orders management

## Security Notes
⚠️ **IMPORTANT**: Change default password in `login.php` before production!

### Recommended Security Improvements
1. Use password hashing instead of plain text
2. Implement rate limiting for login attempts
3. Add IP whitelisting
4. Use HTTPS in production
5. Implement proper user roles and permissions

## Usage
1. Navigate to `/admin_panel/`
2. Login with credentials
3. Manage orders, view statistics
4. Click logout when finished

## Connection to Main System
- Uses same `orders.php` API as main store
- Reads from `data/orders.json`
- Shares assets with main application
