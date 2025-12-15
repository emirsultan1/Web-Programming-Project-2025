<?php
declare(strict_types=1);

// -----------------------------
// Orders Routes
// -----------------------------

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// =====================================================
// GET "MY ORDERS" — LOGGED-IN USER ONLY
// =====================================================
\Flight::route('GET /api/v1/my-orders', function () {

    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $svc  = \Flight::get('OrderService');
    $q    = \Flight::request()->query->data ?? [];
    $user = $_SESSION['user'];

    $out = $svc->listByUser((int)$user['id'], $q);

    \Flight::json([
        'success' => true,
        'data'    => $out,
    ]);
});


// =====================================================
// GET ALL ORDERS — ADMIN ONLY
// =====================================================
\Flight::route('GET /api/v1/orders', function () {

    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('OrderService');
    $q   = \Flight::request()->query->data ?? [];
    $out = $svc->list($q);

    \Flight::json([
        'success' => true,
        'data' => $out
    ]);
});


// =====================================================
// GET SINGLE ORDER — ADMIN OR OWNER
// =====================================================
\Flight::route('GET /api/v1/orders/@id:[0-9]+', function ($id) {

    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $svc = \Flight::get('OrderService');

    try {
        $order = $svc->get((int)$id);
    } catch (\Throwable $e) {
        $code = (int)$e->getCode();
        if ($code < 100 || $code > 599) $code = 500;

        \Flight::json([
            'success' => false,
            'error'   => $e->getMessage(),
        ], $code);
        return;
    }

    $user = $_SESSION['user'];

    // Admin can access any order
    if (($user['role'] ?? '') === 'admin') {
        \Flight::json([
            'success' => true,
            'data' => $order
        ]);
        return;
    }

    // User can only access their own order
    if ((int)($order['user_id'] ?? 0) !== (int)($user['id'] ?? 0)) {
        \Flight::json([
            'success' => false,
            'error' => 'Forbidden: This order does not belong to you.'
        ], 403);
        return;
    }

    \Flight::json([
        'success' => true,
        'data' => $order
    ]);
});


// =====================================================
// CREATE ORDER — LOGGED-IN USER ONLY
// =====================================================
\Flight::route('POST /api/v1/orders', function () {

    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $svc  = \Flight::get('OrderService');
    $data = json_decode(\Flight::request()->getBody(), true) ?? [];

    // Force ownership
    $data['user_id'] = $_SESSION['user']['id'];

    $created = $svc->create($data);

    \Flight::json([
        'success' => true,
        'data' => $created
    ], 201);
});


// =====================================================
// UPDATE ORDER — ADMIN ONLY
// =====================================================
\Flight::route('PUT /api/v1/orders/@id:[0-9]+', function ($id) {

    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc  = \Flight::get('OrderService');
    $data = json_decode(\Flight::request()->getBody(), true) ?? [];

    $updated = $svc->update((int)$id, $data);

    \Flight::json([
        'success' => true,
        'data' => $updated
    ]);
});


// =====================================================
// DELETE ORDER — ADMIN ONLY
// =====================================================
\Flight::route('DELETE /api/v1/orders/@id:[0-9]+', function ($id) {

    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('OrderService');
    $svc->delete((int)$id);

    http_response_code(204);
    return;
});
