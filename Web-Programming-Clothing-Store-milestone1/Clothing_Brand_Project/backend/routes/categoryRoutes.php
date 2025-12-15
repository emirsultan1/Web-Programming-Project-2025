<?php
declare(strict_types=1);

// -----------------------------
// Categories
// -----------------------------

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Public: list categories
\Flight::route('GET /api/v1/categories', function () {
    $svc = \Flight::get('CategoryService');
    $q   = \Flight::request()->query->data ?? [];
    $out = $svc->list($q);

    \Flight::json($out);
});

// Public: get single category
\Flight::route('GET /api/v1/categories/@id:[0-9]+', function ($id) {
    $svc = \Flight::get('CategoryService');
    \Flight::json($svc->get((int)$id));
});

// Create category (ADMIN ONLY)
\Flight::route('POST /api/v1/categories', function () {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('CategoryService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $created = $svc->create($data);

    \Flight::json($created, 201);
});

// Update category (ADMIN ONLY)
\Flight::route('PUT /api/v1/categories/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('CategoryService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $updated = $svc->update((int)$id, $data);

    \Flight::json($updated);
});

// Delete category (ADMIN ONLY)
\Flight::route('DELETE /api/v1/categories/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('CategoryService');
    $svc->delete((int)$id);

    http_response_code(204);
});
