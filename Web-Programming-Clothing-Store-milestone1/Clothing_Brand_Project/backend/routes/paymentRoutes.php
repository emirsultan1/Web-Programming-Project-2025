<?php
declare(strict_types=1);

// -----------------------------
// Payments
// -----------------------------

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// -----------------------------------------------------
// GET /api/v1/payments  (ADMIN ONLY)
// -----------------------------------------------------
\Flight::route('GET /api/v1/payments', function () {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('PaymentService');
    $q   = \Flight::request()->query->data ?? [];
    $out = $svc->list($q);

    \Flight::json($out);
});

// -----------------------------------------------------
// GET /api/v1/payments/:id  (ADMIN OR OWNER via order_id -> orders.user_id)
// -----------------------------------------------------
\Flight::route('GET /api/v1/payments/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $user   = $_SESSION['user'];
    $paySvc = \Flight::get('PaymentService');

    // PaymentService::get throws 404 if not found (handled by global exception handler)
    $payment = $paySvc->get((int)$id);

    // Admin can view any payment
    if (($user['role'] ?? '') === 'admin') {
        \Flight::json($payment);
        return;
    }

    // Normal user: must own the related order
    $orderId = (int)($payment['order_id'] ?? 0);
    if ($orderId <= 0) {
        \Flight::json(["success" => false, "error" => "Forbidden"], 403);
        return;
    }

    $orderSvc = \Flight::get('OrderService');

    // OrderService::get throws 404 if not found
    $order = $orderSvc->get($orderId);

    if ((int)($order['user_id'] ?? 0) !== (int)($user['id'] ?? 0)) {
        \Flight::json(["success" => false, "error" => "Forbidden"], 403);
        return;
    }

    \Flight::json($payment);
});

// -----------------------------------------------------
// POST /api/v1/payments  (LOGGED-IN USER, must own order; admin allowed)
// -----------------------------------------------------
\Flight::route('POST /api/v1/payments', function () {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $user = $_SESSION['user'];

    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $orderId = (int)($data['order_id'] ?? 0);

    if ($orderId <= 0) {
        \Flight::json(["success" => false, "error" => "order_id is required."], 422);
        return;
    }

    // Admin can create payment for any order
    if (($user['role'] ?? '') !== 'admin') {
        $orderSvc = \Flight::get('OrderService');

        // throws 404 if missing
        $order = $orderSvc->get($orderId);

        if ((int)($order['user_id'] ?? 0) !== (int)($user['id'] ?? 0)) {
            \Flight::json([
                "success" => false,
                "error" => "Forbidden: you cannot create a payment for someone else’s order."
            ], 403);
            return;
        }
    }

    $paySvc  = \Flight::get('PaymentService');
    $created = $paySvc->create($data);

    \Flight::json($created, 201);
});

// -----------------------------------------------------
// PUT /api/v1/payments/:id  (ADMIN ONLY)
// -----------------------------------------------------
\Flight::route('PUT /api/v1/payments/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc  = \Flight::get('PaymentService');
    $data = json_decode(\Flight::request()->getBody(), true) ?? [];
    $updated = $svc->update((int)$id, $data);

    \Flight::json($updated);
});

// -----------------------------------------------------
// DELETE /api/v1/payments/:id  (ADMIN ONLY)
// -----------------------------------------------------
\Flight::route('DELETE /api/v1/payments/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('PaymentService');
    $svc->delete((int)$id);

    http_response_code(204);
});
