<?php
declare(strict_types=1);

// -----------------------------
// Users
// -----------------------------

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// -----------------------------------------------------
// GET /api/v1/users  (ADMIN ONLY)
// -----------------------------------------------------
\Flight::route('GET /api/v1/users', function () {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('UserService');
    $q   = \Flight::request()->query->data ?? [];
    $out = $svc->list($q);

    // Safety: never expose password hashes if present
    if (is_array($out)) {
        if (isset($out['items']) && is_array($out['items'])) {
            foreach ($out['items'] as &$u) {
                if (is_array($u) && isset($u['password_hash'])) unset($u['password_hash']);
            }
            unset($u);
        } elseif (isset($out[0]) && is_array($out[0])) {
            foreach ($out as &$u) {
                if (isset($u['password_hash'])) unset($u['password_hash']);
            }
            unset($u);
        }
    }

    \Flight::json($out);
});

// -----------------------------------------------------
// GET /api/v1/users/:id  (ADMIN OR THAT USER ONLY)
// -----------------------------------------------------
\Flight::route('GET /api/v1/users/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    $svc  = \Flight::get('UserService');
    $user = $svc->get((int)$id); // service will throw 404 if missing (your global handler covers it)

    $current = $_SESSION['user'];

    // Admin can see anyone; normal user can only see themselves
    if (($current['role'] ?? '') !== 'admin' && (int)($current['id'] ?? 0) !== (int)($user['user_id'] ?? 0)) {
        \Flight::json([
            'success' => false,
            'error'   => 'Forbidden: you can only view your own user.'
        ], 403);
        return;
    }

    // Safety: never expose password hash
    if (isset($user['password_hash'])) unset($user['password_hash']);

    \Flight::json($user);
});

// -----------------------------------------------------
// POST /api/v1/users  (ADMIN ONLY)
// (Normal users should use /api/register instead.)
// -----------------------------------------------------
\Flight::route('POST /api/v1/users', function () {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('UserService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $created = $svc->create($data);

    if (is_array($created) && isset($created['password_hash'])) unset($created['password_hash']);

    \Flight::json($created, 201);
});

// -----------------------------------------------------
// PUT /api/v1/users/:id  (ADMIN ONLY)
// -----------------------------------------------------
\Flight::route('PUT /api/v1/users/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc     = \Flight::get('UserService');
    $data    = json_decode(\Flight::request()->getBody(), true) ?? [];
    $updated = $svc->update((int)$id, $data);

    if (is_array($updated) && isset($updated['password_hash'])) unset($updated['password_hash']);

    \Flight::json($updated);
});

// -----------------------------------------------------
// DELETE /api/v1/users/:id  (ADMIN ONLY)
// -----------------------------------------------------
\Flight::route('DELETE /api/v1/users/@id:[0-9]+', function ($id) {
    if (!AuthMiddleware::requireRole('admin')) {
        return;
    }

    $svc = \Flight::get('UserService');
    $svc->delete((int)$id);

    http_response_code(204);
});
