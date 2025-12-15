<?php
declare(strict_types=1);

// -----------------------------
// Reviews
// -----------------------------

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

\Flight::route('GET /api/v1/reviews', function () {
    $svc = \Flight::get('ReviewService');
    $q   = \Flight::request()->query->data ?? [];
    $out = $svc->list($q); // e.g. filter by product_id in service

    \Flight::json($out);
});

\Flight::route('GET /api/v1/reviews/@id:[0-9]+', function ($id) {
    $svc    = \Flight::get('ReviewService');
    $review = $svc->get((int)$id);

    if (!$review) {
        \Flight::json(['success' => false, 'error' => 'Review not found'], 404);
        return;
    }

    \Flight::json($review);
});

// -----------------------------------------------------
// POST /api/v1/reviews  (LOGGED-IN USER ONLY)
// -----------------------------------------------------
\Flight::route('POST /api/v1/reviews', function () {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $svc  = \Flight::get('ReviewService');
    $data = json_decode(\Flight::request()->getBody(), true) ?? [];

    // Force review to belong to the logged-in user
    $data['user_id'] = $_SESSION['user']['id'];

    $created = $svc->create($data);

    \Flight::json($created, 201);
});

// -----------------------------------------------------
// PUT /api/v1/reviews/:id  (OWNER OR ADMIN)
// -----------------------------------------------------
\Flight::route('PUT /api/v1/reviews/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $svc    = \Flight::get('ReviewService');
    $review = $svc->get((int)$id);

    if (!$review) {
        \Flight::json(["success" => false, "error" => "Review not found"], 404);
        return;
    }

    $user = $_SESSION['user'];

    // Admin can update any review; user can update only their own
    if (($user['role'] ?? '') !== 'admin' && (int)($review['user_id'] ?? 0) !== (int)($user['id'] ?? 0)) {
        \Flight::json([
            "success" => false,
            "error"   => "Forbidden: you can only edit your own reviews."
        ], 403);
        return;
    }

    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $updated = $svc->update((int)$id, $data);

    \Flight::json($updated);
});

// -----------------------------------------------------
// DELETE /api/v1/reviews/:id  (OWNER OR ADMIN)
// -----------------------------------------------------
\Flight::route('DELETE /api/v1/reviews/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $svc    = \Flight::get('ReviewService');
    $review = $svc->get((int)$id);

    if (!$review) {
        \Flight::json(["success" => false, "error" => "Review not found"], 404);
        return;
    }

    $user = $_SESSION['user'];

    // Admin can delete any review; user can delete only their own
    if (($user['role'] ?? '') !== 'admin' && (int)($review['user_id'] ?? 0) !== (int)($user['id'] ?? 0)) {
        \Flight::json([
            "success" => false,
            "error"   => "Forbidden: you can only delete your own reviews."
        ], 403);
        return;
    }

    $svc->delete((int)$id);
    http_response_code(204);
});
