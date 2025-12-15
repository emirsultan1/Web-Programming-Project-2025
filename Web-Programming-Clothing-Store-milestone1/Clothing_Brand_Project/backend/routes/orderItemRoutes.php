<?php
declare(strict_types=1);

// -----------------------------
// Order Items
// -----------------------------

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// List order items (ADMIN ONLY)
\Flight::route('GET /api/v1/order-items', function () {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('OrderItemService');
    $q   = \Flight::request()->query->data ?? [];
    $out = $svc->list($q); // supports order_id filter in service

    \Flight::json($out);
});

// View items for ONE order (ADMIN OR OWNER)
// GET /api/v1/orders/:id/items
\Flight::route('GET /api/v1/orders/@id:[0-9]+/items', function ($id) {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $user = $_SESSION['user'];

    // Admin can view any order items
    if (($user['role'] ?? '') !== 'admin') {
        $orderSvc = \Flight::get('OrderService');
        $order    = $orderSvc->get((int)$id); // throws 404 if missing

        if ((int)($order['user_id'] ?? 0) !== (int)($user['id'] ?? 0)) {
            \Flight::json(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }
    }

    $itemSvc = \Flight::get('OrderItemService');
    $out     = $itemSvc->list(['order_id' => (int)$id]);

    \Flight::json(['success' => true, 'data' => $out]);
});

// Create order item (ADMIN ONLY)
\Flight::route('POST /api/v1/order-items', function () {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('OrderItemService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $created = $svc->create($data);

    \Flight::json($created, 201);
});

// Update order item (ADMIN ONLY)
\Flight::route('PUT /api/v1/order-items/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('OrderItemService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $updated = $svc->update((int)$id, $data);

    \Flight::json($updated);
});

// Delete order item (ADMIN ONLY)
\Flight::route('DELETE /api/v1/order-items/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('OrderItemService');
    $svc->delete((int)$id);

    http_response_code(204);
});
