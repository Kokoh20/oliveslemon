<?php
declare(strict_types=1);

require __DIR__ . '/database/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$db = get_db();

$products = $db->query('SELECT * FROM products ORDER BY category, name')->fetchAll();

$result = [];
foreach ($products as $p) {
    $variants = $db->prepare('SELECT variant_key AS "key", name, price FROM product_variants WHERE product_id = ? ORDER BY price');
    $variants->execute([$p['id']]);

    $result[] = [
        'id'       => $p['id'],
        'name'     => $p['name'],
        'category' => $p['category'],
        'image'    => $p['image'],
        'variants' => $variants->fetchAll(),
    ];
}

http_response_code(200);
echo json_encode(['products' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
