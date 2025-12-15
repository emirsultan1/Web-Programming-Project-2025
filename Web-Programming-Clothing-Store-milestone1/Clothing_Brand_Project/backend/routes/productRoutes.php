<?php
declare(strict_types=1);

// -----------------------------
// Products
// -----------------------------

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Public: list products
\Flight::route('GET /api/v1/products', function () {
    $svc = \Flight::get('ProductService');
    $q   = \Flight::request()->query->data ?? [];
    $out = $svc->list($q);

    \Flight::json($out);
});

// Public: get single product
\Flight::route('GET /api/v1/products/@id:[0-9]+', function ($id) {
    $svc = \Flight::get('ProductService');
    $out = $svc->get((int)$id);

    \Flight::json($out);
});

// Create product (ADMIN ONLY)
\Flight::route('POST /api/v1/products', function () {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('ProductService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $created = $svc->create($data);

    \Flight::json($created, 201);
});

// Update product (ADMIN ONLY)
\Flight::route('PUT /api/v1/products/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('ProductService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $updated = $svc->update((int)$id, $data);

    \Flight::json($updated);
});

// Delete product (ADMIN ONLY)
\Flight::route('DELETE /api/v1/products/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('ProductService');
    $svc->delete((int)$id);

    // 204 = No Content (no JSON body)
    http_response_code(204);
});
