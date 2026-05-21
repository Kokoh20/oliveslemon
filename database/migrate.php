<?php
declare(strict_types=1);

/**
 * Database migration & seed script.
 * Run: php database/migrate.php
 *
 * - Creates the SQLite database and tables
 * - Seeds admin user with hashed password
 * - Seeds the product catalog from shop data
 * - Optionally migrates existing orders from data/orders.json
 */

require __DIR__ . '/db.php';

echo "=== Olives & Lemon Database Migration ===\n\n";

$db = get_db();

// --- Apply schema ---
echo "[1/4] Applying schema...\n";
$schema = file_get_contents(__DIR__ . '/schema.sql');
$db->exec($schema);
echo "  Tables created.\n\n";

// --- Seed admin user ---
echo "[2/4] Seeding admin user...\n";
$stmt = $db->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ?');
$stmt->execute(['admin']);
if ((int)$stmt->fetchColumn() === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->prepare('INSERT INTO admin_users (username, password) VALUES (?, ?)')
       ->execute(['admin', $hash]);
    echo "  Admin user created (admin / admin123).\n\n";
} else {
    echo "  Admin user already exists, skipping.\n\n";
}

// --- Seed products ---
echo "[3/4] Seeding product catalog...\n";

function slugify(string $s): string {
    return preg_replace('/(^-|-$)/', '', preg_replace('/[^a-z0-9]+/', '-', strtolower($s)));
}

$categories = [
    'coffee-iced'    => 'Coffee Based (Iced Coffee)',
    'frappuccino'    => 'Frappuccino',
    'hot-drinks'     => 'Hot Drinks',
    'oat-milk'       => 'Oat Milk Based',
    'coffee-special' => 'Coffee Special',
    'non-coffee'     => 'Non Coffee',
    'matcha-series'  => 'Matcha Series',
    'juices'         => 'Juices',
    'appetizers'     => 'Appetizers',
    'salad'          => 'Salad',
    'pasta'          => 'Pasta',
    'rice-meals'     => 'Rice Meals',
    'sandwich'       => 'Sandwich',
];

$productsData = [
    // Coffee Based (Iced Coffee)
    ['coffee-iced', 'Cappuccino', [['medium',115],['large',135]], 'assets/images/capuccino.jpg'],
    ['coffee-iced', 'Vietnamese Latte', [['medium',115],['large',135]], 'assets/images/Easy_Organic_Vietnamese_Coffee_Recipe.jpg'],
    ['coffee-iced', 'Macadamia', [['medium',115],['large',135]], 'assets/images/macadamia.jpg'],
    ['coffee-iced', 'Butterscotch', [['medium',115],['large',135]], 'assets/images/Starbucks-Smoked-Butterscotch-Latte-Pin-5.jpg'],
    ['coffee-iced', 'Mocha', [['medium',115],['large',135]], 'assets/images/mocha.jpg'],
    ['coffee-iced', 'White Mocha', [['medium',115],['large',135]], 'assets/images/white vanilla.jpg'],
    ['coffee-iced', 'French Vanilla', [['medium',115],['large',135]], 'assets/images/french vanilla (2).jpg'],
    ['coffee-iced', 'Salted Caramel', [['medium',115],['large',135]], 'assets/images/salted caramel.jpg'],
    ['coffee-iced', 'Cereal Latte', [['medium',115],['large',135]], 'assets/images/coffee6.jpg'],

    // Frappuccino
    ['frappuccino', 'Biscoff', [['16oz Medium',140],['22oz Large',165]], 'assets/images/Biscoff.jpg'],
    ['frappuccino', 'Java Chips', [['16oz Medium',150],['22oz Large',165]], 'assets/images/Java chips.jpg'],
    ['frappuccino', 'Caramel Macchiato', [['16oz Medium',150],['22oz Large',165]], 'assets/images/caramel macchiato.jpg'],
    ['frappuccino', 'Dark Mocha', [['16oz Medium',150],['22oz Large',165]], 'assets/images/Dark Mocha.jpg'],
    ['frappuccino', 'Milo Dinosaur', [['16oz Medium',140],['22oz Large',165]], 'assets/images/Milo Dinosaur.png'],
    ['frappuccino', 'Matcha', [['16oz Medium',140],['22oz Large',165]], 'assets/images/Matcha.jpg'],
    ['frappuccino', 'Oreo', [['16oz Medium',140],['22oz Large',165]], 'assets/images/Oreo.jpg'],
    ['frappuccino', 'Strawberry', [['16oz Medium',140],['22oz Large',165]], 'assets/images/Strawberry.jpg'],
    ['frappuccino', "Pulo't Frappe", [['16oz Medium',150],['22oz Large',165]], 'assets/images/Pulot.jpg'],
    ['frappuccino', 'Homemade Chocolate', [['16oz Medium',155],['22oz Large',165]], 'assets/images/Homemade Chocolate.jpg'],
    ['frappuccino', 'Blueberry Frappe', [['16oz Medium',140],['22oz Large',165]], 'assets/images/Blueberry.jpg'],

    // Hot Drinks
    ['hot-drinks', 'Matcha', [['medium',140],['large',160]], 'assets/images/matcha hot.jpg'],
    ['hot-drinks', "Pulo't", [['medium',150],['large',170]], 'assets/images/pulot hot.jpg'],
    ['hot-drinks', 'Cappuccino', [['medium',140],['large',160]], 'assets/images/cappuccino.jpg'],
    ['hot-drinks', 'Macadamia', [['medium',140],['large',160]], 'assets/images/macadamia hot.jpg'],
    ['hot-drinks', 'Mocha', [['medium',140],['large',160]], 'assets/images/mocha hot.jpg'],
    ['hot-drinks', 'Vietnamese', [['medium',140],['large',160]], 'assets/images/vietnamese hot.jpg'],
    ['hot-drinks', 'Hot Chocolate', [['medium',140],['large',160]], 'assets/images/hot chocolate.jpg'],
    ['hot-drinks', 'Espanner Latte', [['medium',140],['large',160]], 'assets/images/enspanner.jpg'],

    // Oat Milk Based
    ['oat-milk', 'Oat Latte', [['medium',140],['large',160]], 'assets/images/oat.jpg'],
    ['oat-milk', 'Spanish Oat Latte', [['medium',140],['large',160]], 'assets/images/spanish.jpg'],
    ['oat-milk', 'White Mocha Oat', [['medium',140],['large',160]], 'assets/images/white.jpg'],
    ['oat-milk', 'Caramel Oat', [['medium',140],['large',160]], 'assets/images/caramel.jpg'],

    // Coffee Special
    ['coffee-special', "Pulo't Coffee", [['medium',140],['large',160]], 'assets/images/pulot hot.jpg'],
    ['coffee-special', 'Tiramisu', [['medium',145],['large',170]], 'assets/images/tiramisu.jpg'],
    ['coffee-special', 'Barista Drink', [['medium',145],['large',170]], 'assets/images/barista.jpg'],
    ['coffee-special', 'Caramel Macchiato', [['medium',115],['large',135]], 'assets/images/caramel hot.jpg'],
    ['coffee-special', 'Sea Salt Latte', [['medium',115],['large',135]], 'assets/images/seasalt.jpg'],
    ['coffee-special', 'Biscoff', [['medium',115],['large',150]], 'assets/images/biscoff.jpg'],
    ['coffee-special', 'Espanner Latte', [['medium',115],['large',140]], 'assets/images/Einspanner.jpg'],

    // Non Coffee
    ['non-coffee', 'Cocoa Dark Choco', [['medium',95],['large',115]], 'assets/images/cocoa.jpg'],
    ['non-coffee', 'Chocolate Float', [['medium',100],['large',120]], 'assets/images/float.jpg'],
    ['non-coffee', 'Strawberry Milk', [['medium',90],['large',110]], 'assets/images/strawberry float.jpg'],
    ['non-coffee', 'Blueberry Milk', [['medium',90],['large',110]], 'assets/images/blueberrymilk.jpg'],
    ['non-coffee', 'Passion Fruit Tea', [['medium',90],['large',110]], 'assets/images/passion.jpg'],

    // Matcha Series
    ['matcha-series', 'Matcha Latte', [['medium',110],['large',125]], 'assets/images/matchal.jpg'],
    ['matcha-series', 'Spanish Matcha', [['medium',110],['large',140]], 'assets/images/spanishmatcha.jpg'],
    ['matcha-series', 'Oat Milk Matcha', [['medium',130],['large',150]], 'assets/images/oatmatcha.png'],
    ['matcha-series', 'Ice Cream Matcha', [['medium',120],['large',140]], 'assets/images/icematcha.jpg'],
    ['matcha-series', 'White Choco Matcha', [['medium',110],['large',130]], 'assets/images/whitematcha.jpg'],
    ['matcha-series', 'Dirty Matcha', [['medium',130],['large',150]], 'assets/images/dirtymatcha.jpg'],
    ['matcha-series', 'Strawberry Matcha', [['medium',120],['large',140]], 'assets/images/strawberrymatcha.jpg'],

    // Juices
    ['juices', 'Aloe Vera', [['medium',70],['large',90]], 'assets/images/aloe.jpg'],
    ['juices', 'Lemonade', [['medium',70],['large',90]], 'assets/images/lemon.jpg'],
    ['juices', 'Mango Lemonade', [['medium',90],['large',110]], 'assets/images/mango.jpg'],
    ['juices', 'Mandarin', [['medium',70],['large',105]], 'assets/images/mandarin.jpeg'],
    ['juices', 'Frozen Peppermint', [['medium',140],['large',160]], 'assets/images/peppermint.jpg'],
    ['juices', 'Citrus Sunset', [['medium',90],['large',110]], 'assets/images/sunset.jpg'],

    // Appetizers
    ['appetizers', 'Nachos', [['regular',170]], 'assets/images/nachos.jpg'],
    ['appetizers', 'Cheesy Quesadillas', [['regular',150]], 'assets/images/Cheese.jpg'],

    // Salad
    ['salad', 'Olives and Lemon Salad', [['regular',200]], 'assets/images/salad.jpg'],

    // Pasta
    ['pasta', 'Pesto Pasta', [['regular',230]], 'assets/images/pesto.jpg'],
    ['pasta', 'Bolognese Pasta', [['regular',180]], 'assets/images/bolognese.jpg'],
    ['pasta', 'Lasagna', [['regular',185]], 'assets/images/Lasagna.jpg'],
    ['pasta', 'Chicken Pasta', [['regular',175]], 'assets/images/chick.jpg'],
    ['pasta', 'Creamy Carbonara', [['regular',170]], 'assets/images/carbonara.jpg'],
    ['pasta', 'Spanish Sardines', [['regular',170]], 'assets/images/sradines.jpg'],
    ['pasta', 'Oglio', [['regular',170]], 'assets/images/oglio.jpg'],

    // Rice Meals
    ['rice-meals', 'Fish Fillet with Veggies', [['regular',180]], 'assets/images/fish.jpg'],
    ['rice-meals', 'Chicken Pesto', [['regular',230]], 'assets/images/pasta.jpg'],
    ['rice-meals', 'Salisbury Steak (Double Patty)', [['regular',165]], 'assets/images/salisbury.jpg'],
    ['rice-meals', 'Mongolian Beef', [['regular',190]], 'assets/images/beef.jpg'],
    ['rice-meals', 'Steak and Fries', [['regular',350]], 'assets/images/fries.jpg'],

    // Sandwich
    ['sandwich', 'New Yorker', [['regular',95]], 'assets/images/new.jpg'],
    ['sandwich', 'Grilled Cheese', [['regular',90]], 'assets/images/grill.jpg'],
    ['sandwich', 'Garlic Bread', [['regular',60]], 'assets/images/garlic.jpg'],
    ['sandwich', 'Ham & Cheese', [['regular',95]], 'assets/images/ham.jpg'],
];

$extrasData = [
    'coffee-iced'    => [['milk','milk',15],['espresso','espresso',15],['sugar','sugar',10],['choc-syrup','choc syrup',15],['cocoa','cocoa powder',10],['ice','ice',10]],
    'frappuccino'    => [['whip','whipped cream',15],['choc-syrup','choc syrup',15],['java-chips','java chips',20],['oreo-crumble','oreo crumble',15],['strawberry-drizzle','strawberry drizzle',15]],
    'hot-drinks'     => [['extra-shot','extra espresso shot',20],['milk','milk',10],['sugar','sugar',5]],
    'oat-milk'       => [['oat-extra','extra oat milk',20],['syrup-vanilla','vanilla syrup',15],['syrup-caramel','caramel syrup',15]],
    'coffee-special' => [['extra-shot','extra espresso shot',20],['syrup-caramel','caramel syrup',15],['syrup-hazelnut','hazelnut syrup',15]],
    'matcha-series'  => [['matcha-shot','extra matcha shot',20],['milk','milk',10],['sugar','sugar',5]],
    'non-coffee'     => [['ice','extra ice',5],['sweetness','extra sweetness',5]],
    'juices'         => [['ice','extra ice',5],['sweetness','extra sweetness',5]],
    'appetizers'     => [['extra-cheese','extra cheese',20],['salsa','extra salsa',15]],
    'salad'          => [['dressing','extra dressing',15],['croutons','croutons',10]],
    'pasta'          => [['cheese','extra cheese',20],['sauce','extra sauce',15],['garlic','garlic',10]],
    'rice-meals'     => [['egg','sunny side up egg',15],['kimchi','kimchi',15],['lettuce','lettuce',15],['corn','corn',10],['seaweeds','seaweeds',10],['pickles','pickles',10]],
    'sandwich'       => [['cheese','extra cheese',15],['ham','extra ham',20],['lettuce','lettuce',10]],
];

// Clear existing products
$db->exec('DELETE FROM product_extras');
$db->exec('DELETE FROM product_variants');
$db->exec('DELETE FROM products');

$insertProduct = $db->prepare('INSERT INTO products (id, name, category, image) VALUES (?, ?, ?, ?)');
$insertVariant = $db->prepare('INSERT INTO product_variants (product_id, variant_key, name, price) VALUES (?, ?, ?, ?)');
$insertExtra   = $db->prepare('INSERT INTO product_extras (category, extra_key, name, price) VALUES (?, ?, ?, ?)');

$count = 0;
foreach ($productsData as [$cat, $name, $variants, $image]) {
    $id = $cat . '-' . slugify($name);
    $insertProduct->execute([$id, $name, $cat, $image]);
    foreach ($variants as [$vKey, $vPrice]) {
        $insertVariant->execute([$id, slugify($vKey), ucfirst($vKey), $vPrice]);
    }
    $count++;
}
echo "  $count products seeded.\n";

$extrasCount = 0;
foreach ($extrasData as $cat => $extras) {
    foreach ($extras as [$key, $name, $price]) {
        $insertExtra->execute([$cat, $key, $name, $price]);
        $extrasCount++;
    }
}
echo "  $extrasCount extras seeded.\n\n";

// --- Migrate existing JSON orders ---
echo "[4/4] Migrating existing orders from JSON...\n";
$jsonFile = dirname(__DIR__) . '/data/orders.json';
if (file_exists($jsonFile)) {
    $raw = file_get_contents($jsonFile);
    $jsonOrders = json_decode($raw, true);
    if (is_array($jsonOrders) && count($jsonOrders) > 0) {
        $insertOrder = $db->prepare(
            'INSERT OR IGNORE INTO orders (id, created_at, status, customer_name, customer_phone, customer_address, customer_type, subtotal, discount, payable, payment_method, payment_paid, payment_ref) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertItem = $db->prepare(
            'INSERT INTO order_items (order_id, product_id, name, variant_key, variant_name, price, qty, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertItemExtra = $db->prepare(
            'INSERT INTO order_item_extras (order_item_id, extra_key, name, price, qty) VALUES (?, ?, ?, ?, ?)'
        );

        $migrated = 0;
        $db->beginTransaction();
        foreach ($jsonOrders as $o) {
            $c = $o['customer'] ?? [];
            $t = $o['totals'] ?? [];
            $p = $o['payment'] ?? [];
            $insertOrder->execute([
                $o['id'] ?? uniqid('ord_', true),
                $o['createdAt'] ?? date('c'),
                $o['status'] ?? 'new',
                $c['name'] ?? '',
                $c['phone'] ?? '',
                $c['address'] ?? '',
                $c['type'] ?? 'pickup',
                (float)($t['subtotal'] ?? 0),
                (float)($t['discount'] ?? 0),
                (float)($t['payable'] ?? 0),
                $p['method'] ?? 'cash',
                ($p['paid'] ?? false) ? 1 : 0,
                $p['ref'] ?? '',
            ]);

            foreach ($o['items'] ?? [] as $item) {
                $v = $item['variant'] ?? [];
                $insertItem->execute([
                    $o['id'],
                    $item['productId'] ?? $item['id'] ?? '',
                    $item['name'] ?? '',
                    $v['key'] ?? $item['variantKey'] ?? '',
                    $v['name'] ?? $item['variantName'] ?? '',
                    (float)($item['price'] ?? 0),
                    (int)($item['qty'] ?? 1),
                    $item['notes'] ?? '',
                ]);
                $itemId = (int)$db->lastInsertId();
                foreach ($item['extras'] ?? [] as $extra) {
                    if (($extra['qty'] ?? 0) > 0) {
                        $insertItemExtra->execute([
                            $itemId,
                            $extra['key'] ?? '',
                            $extra['name'] ?? '',
                            (float)($extra['price'] ?? 0),
                            (int)($extra['qty'] ?? 1),
                        ]);
                    }
                }
            }
            $migrated++;
        }
        $db->commit();
        echo "  $migrated orders migrated from JSON.\n";
    } else {
        echo "  No orders to migrate.\n";
    }
} else {
    echo "  No JSON file found, skipping migration.\n";
}

echo "\nDone! Database created at: database/olives_lemon.db\n";
