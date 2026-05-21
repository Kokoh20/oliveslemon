-- Olives & Lemon Database Schema (SQLite)

CREATE TABLE IF NOT EXISTS admin_users (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    username    TEXT    NOT NULL UNIQUE,
    password    TEXT    NOT NULL,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS orders (
    id              TEXT    PRIMARY KEY,
    created_at      TEXT    NOT NULL,
    status          TEXT    NOT NULL DEFAULT 'new',
    customer_name   TEXT    NOT NULL DEFAULT '',
    customer_phone  TEXT    NOT NULL DEFAULT '',
    customer_address TEXT   NOT NULL DEFAULT '',
    customer_type   TEXT    NOT NULL DEFAULT 'pickup',
    subtotal        REAL    NOT NULL DEFAULT 0,
    discount        REAL    NOT NULL DEFAULT 0,
    payable         REAL    NOT NULL DEFAULT 0,
    payment_method  TEXT    NOT NULL DEFAULT 'cash',
    payment_paid    INTEGER NOT NULL DEFAULT 0,
    payment_ref     TEXT    NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS order_items (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id    TEXT    NOT NULL,
    product_id  TEXT    NOT NULL DEFAULT '',
    name        TEXT    NOT NULL,
    variant_key TEXT    NOT NULL DEFAULT '',
    variant_name TEXT   NOT NULL DEFAULT '',
    price       REAL    NOT NULL DEFAULT 0,
    qty         INTEGER NOT NULL DEFAULT 1,
    notes       TEXT    NOT NULL DEFAULT '',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_item_extras (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    order_item_id   INTEGER NOT NULL,
    extra_key       TEXT    NOT NULL DEFAULT '',
    name            TEXT    NOT NULL,
    price           REAL    NOT NULL DEFAULT 0,
    qty             INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS products (
    id          TEXT    PRIMARY KEY,
    name        TEXT    NOT NULL,
    category    TEXT    NOT NULL,
    image       TEXT    NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS product_variants (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id  TEXT    NOT NULL,
    variant_key TEXT    NOT NULL,
    name        TEXT    NOT NULL,
    price       REAL    NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS product_extras (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    category    TEXT    NOT NULL,
    extra_key   TEXT    NOT NULL,
    name        TEXT    NOT NULL,
    price       REAL    NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_orders_status     ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_created     ON orders(created_at);
CREATE INDEX IF NOT EXISTS idx_order_items_order   ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_extras_item   ON order_item_extras(order_item_id);
CREATE INDEX IF NOT EXISTS idx_products_category   ON products(category);
CREATE INDEX IF NOT EXISTS idx_variants_product    ON product_variants(product_id);
CREATE INDEX IF NOT EXISTS idx_extras_category     ON product_extras(category);
