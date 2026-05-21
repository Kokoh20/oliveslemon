<?php
declare(strict_types=1);

require __DIR__ . '/database/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function send_json($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function get_json_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Build a full order array (matching the old JSON format) from the DB row + items.
 */
function build_order(PDO $db, array $row): array {
    $items = $db->prepare(
        'SELECT id, product_id, name, variant_key, variant_name, price, qty, notes FROM order_items WHERE order_id = ?'
    );
    $items->execute([$row['id']]);
    $itemRows = $items->fetchAll();

    $orderItems = [];
    foreach ($itemRows as $ir) {
        $extras = $db->prepare(
            'SELECT extra_key AS "key", name, price, qty FROM order_item_extras WHERE order_item_id = ?'
        );
        $extras->execute([$ir['id']]);
        $extrasArr = $extras->fetchAll();

        $orderItems[] = [
            'productId'   => $ir['product_id'],
            'name'        => $ir['name'],
            'variant'     => ['key' => $ir['variant_key'], 'name' => $ir['variant_name']],
            'variantKey'  => $ir['variant_key'],
            'variantName' => $ir['variant_name'],
            'price'       => (float)$ir['price'],
            'qty'         => (int)$ir['qty'],
            'notes'       => $ir['notes'],
            'extras'      => $extrasArr,
        ];
    }

    return [
        'id'        => $row['id'],
        'createdAt' => $row['created_at'],
        'status'    => $row['status'],
        'customer'  => [
            'name'    => $row['customer_name'],
            'phone'   => $row['customer_phone'],
            'address' => $row['customer_address'],
            'type'    => $row['customer_type'],
        ],
        'items'   => $orderItems,
        'totals'  => [
            'subtotal' => (float)$row['subtotal'],
            'discount' => (float)$row['discount'],
            'payable'  => (float)$row['payable'],
        ],
        'payment' => [
            'method' => $row['payment_method'],
            'paid'   => (bool)$row['payment_paid'],
            'ref'    => $row['payment_ref'],
        ],
    ];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$db = get_db();

// ─── GET ──────────────────────────────────────────────
if ($method === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) send_json(['error' => 'Order not found'], 404);
        send_json(['order' => build_order($db, $row)]);
    }

    $rows = $db->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
    $orders = array_map(fn($r) => build_order($db, $r), $rows);
    send_json(['orders' => $orders]);
}

// ─── POST ─────────────────────────────────────────────
if ($method === 'POST') {
    $payload  = get_json_input();
    $customer = $payload['customer'] ?? [];
    $items    = $payload['items'] ?? [];
    $totals   = $payload['totals'] ?? [];
    $payment  = $payload['payment'] ?? [];

    if (!$customer || !$items) {
        send_json(['error' => 'Missing customer or items'], 422);
    }

    $now = (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format(DateTimeInterface::RFC3339);
    $orderId = uniqid('ord_', true);

    $db->beginTransaction();
    try {
        $db->prepare(
            'INSERT INTO orders (id, created_at, status, customer_name, customer_phone, customer_address, customer_type, subtotal, discount, payable, payment_method, payment_paid, payment_ref) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $orderId,
            $now,
            'new',
            trim((string)($customer['name'] ?? '')),
            trim((string)($customer['phone'] ?? '')),
            trim((string)($customer['address'] ?? '')),
            (string)($customer['type'] ?? 'pickup'),
            (float)($totals['subtotal'] ?? 0),
            (float)($totals['discount'] ?? 0),
            (float)($totals['payable'] ?? 0),
            (string)($payment['method'] ?? 'cash'),
            ($payment['paid'] ?? false) ? 1 : 0,
            (string)($payment['ref'] ?? ''),
        ]);

        $insertItem = $db->prepare(
            'INSERT INTO order_items (order_id, product_id, name, variant_key, variant_name, price, qty, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertExtra = $db->prepare(
            'INSERT INTO order_item_extras (order_item_id, extra_key, name, price, qty) VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($items as $item) {
            $v = $item['variant'] ?? [];
            $insertItem->execute([
                $orderId,
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
                    $insertExtra->execute([
                        $itemId,
                        $extra['key'] ?? '',
                        $extra['name'] ?? '',
                        (float)($extra['price'] ?? 0),
                        (int)($extra['qty'] ?? 1),
                    ]);
                }
            }
        }

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        send_json(['error' => 'Failed to save order'], 500);
    }

    // Return the created order
    $stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $row = $stmt->fetch();
    send_json(['ok' => true, 'order' => build_order($db, $row)]);
}

// ─── PATCH ────────────────────────────────────────────
if ($method === 'PATCH') {
    $payload = get_json_input();
    $id = (string)($payload['id'] ?? ($_GET['id'] ?? ''));
    if ($id === '') send_json(['error' => 'Missing id'], 422);

    // Check existence
    $stmt = $db->prepare('SELECT id FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) send_json(['error' => 'Order not found'], 404);

    $sets = [];
    $params = [];

    if (isset($payload['status'])) {
        $sets[] = 'status = ?';
        $params[] = (string)$payload['status'];
    }
    if (isset($payload['customer']) && is_array($payload['customer'])) {
        foreach (['name' => 'customer_name', 'phone' => 'customer_phone', 'address' => 'customer_address', 'type' => 'customer_type'] as $k => $col) {
            if (isset($payload['customer'][$k])) {
                $sets[] = "$col = ?";
                $params[] = (string)$payload['customer'][$k];
            }
        }
    }
    if (isset($payload['totals']) && is_array($payload['totals'])) {
        foreach (['subtotal', 'discount', 'payable'] as $k) {
            if (isset($payload['totals'][$k])) {
                $sets[] = "$k = ?";
                $params[] = (float)$payload['totals'][$k];
            }
        }
    }

    if (!empty($sets)) {
        $params[] = $id;
        $db->prepare('UPDATE orders SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
    }

    send_json(['ok' => true]);
}

// ─── DELETE ───────────────────────────────────────────
if ($method === 'DELETE') {
    $id = (string)($_GET['id'] ?? '');
    if ($id === '') {
        $payload = get_json_input();
        $id = (string)($payload['id'] ?? '');
    }
    if ($id === '') send_json(['error' => 'Missing id'], 422);

    $stmt = $db->prepare('DELETE FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) send_json(['error' => 'Order not found'], 404);

    send_json(['ok' => true]);
}

send_json(['error' => 'Method not allowed'], 405);
