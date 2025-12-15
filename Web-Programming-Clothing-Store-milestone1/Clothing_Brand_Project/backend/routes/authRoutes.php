<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/UsersDAO.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// ==========================================
// Auth: Register
// POST /api/register
// ==========================================
\Flight::route('POST /api/register', function () {
    $request = \Flight::request();

    // form data OR JSON
    $data = $request->data->getData();
    if (empty($data)) {
        $json = json_decode($request->getBody(), true);
        if (is_array($json)) $data = $json;
    }

    $name  = trim((string)($data['name'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));

    // IMPORTANT: do NOT trim passwords (spaces could be valid)
    $password        = (string)($data['password'] ?? '');
    $passwordConfirm = (string)($data['password_confirm'] ?? $password);

    $errors = [];

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        \Flight::json(['success' => false, 'errors' => $errors], 422);
        return;
    }

    $usersDao = new \UsersDAO();

    if ($usersDao->isEmailTaken($email)) {
        \Flight::json([
            'success' => false,
            'errors'  => ['This email is already registered.'],
        ], 409);
        return;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // NOTE: keep 'customer' only if this matches your DB enum/values.
    // If your DB uses 'user' instead, change it here.
    $userId = $usersDao->createUser($name, $email, $passwordHash, 'customer');

    \Flight::json([
        'success' => true,
        'message' => 'User registered successfully.',
        'user_id' => (int)$userId,
    ], 201);
});

// ==========================================
// Auth: Login
// POST /api/login
// ==========================================
\Flight::route('POST /api/login', function () {
    $request = \Flight::request();

    $data = $request->data->getData();
    if (empty($data)) {
        $json = json_decode($request->getBody(), true);
        if (is_array($json)) $data = $json;
    }

    $email = trim((string)($data['email'] ?? ''));

    // IMPORTANT: do NOT trim password
    $password = (string)($data['password'] ?? '');

    if ($email === '' || $password === '') {
        \Flight::json([
            'success' => false,
            'errors'  => ['Email and password are required.'],
        ], 422);
        return;
    }

    $usersDao = new \UsersDAO();
    $user     = $usersDao->findByEmail($email);

    if (!$user || !password_verify($password, (string)$user['password_hash'])) {
        error_log("Failed login attempt for email: {$email}");
        \Flight::json([
            'success' => false,
            'errors'  => ['Invalid email or password.'],
        ], 401);
        return;
    }

    // Start session (cookie params already set in index.php)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // IMPORTANT: prevent session fixation
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'    => (int)$user['user_id'],
        'name'  => (string)$user['name'],
        'email' => (string)$user['email'],
        'role'  => (string)$user['role'],
    ];

    \Flight::json([
        'success' => true,
        'message' => 'Login successful.',
        'user'    => $_SESSION['user'],
    ]);
});

// ==========================================
// Auth: Current user
// GET /api/me
// ==========================================
\Flight::route('GET /api/me', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user'])) {
        \Flight::json([
            'authenticated' => false,
            'user'          => null,
        ]);
        return;
    }

    \Flight::json([
        'authenticated' => true,
        'user'          => $_SESSION['user'],
    ]);
});

// ==========================================
// Auth: Protected test route
// GET /api/secret
// ==========================================
\Flight::route('GET /api/secret', function () {
    if (!AuthMiddleware::requireLogin()) {
        return;
    }

    \Flight::json([
        'success' => true,
        'message' => 'You are allowed to see this secret route.',
        'user'    => $_SESSION['user'],
    ]);
});

// ==========================================
// Auth: Logout
// POST /api/logout
// ==========================================
\Flight::route('POST /api/logout', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = [];

    // Properly expire session cookie (include SameSite to match your setup)
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        // PHP 7.3+ supports options array (includes samesite)
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'] ?? '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => (bool)($params['secure'] ?? false),
            'httponly' => (bool)($params['httponly'] ?? true),
            'samesite' => 'Lax',
        ]);
    }

    session_destroy();

    \Flight::json([
        'success' => true,
        'message' => 'Logged out successfully.',
    ]);
});
