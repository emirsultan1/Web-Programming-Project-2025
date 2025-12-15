<?php
declare(strict_types=1);

// ------------------------------------
// Session cookie settings (localhost, same-site)
// ------------------------------------
// We keep SameSite=Lax (default) which works for same-site requests
// like http://localhost:5500 -> http://localhost:8000
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,   // true only if you use HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// ---- Static file passthrough for PHP built-in server ----
// Serve real files (e.g., /openapi.yaml) and directory index.html (e.g., /docs/)
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $abs  = __DIR__ . $path;

    // If it's an actual file, let the built-in server serve it
    if ($path && $path !== '/' && is_file($abs)) {
        return false;
    }

    // If it's a directory, try serving its index.html
    if ($path && is_dir($abs)) {
        $index = rtrim($abs, '/\\') . '/index.html';
        if (is_file($index)) {
            // Minimal headers; PHP built-in server will infer type, but set explicitly for safety
            header('Content-Type: text/html; charset=utf-8');
            readfile($index);
            exit;
        }
    }
}

// ------------------------------------
// Composer autoload (loads Flight etc.)
// ------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';

// ------------------------------------
// App bootstrap (register DAOs/Services)
// ------------------------------------
require_once __DIR__ . '/../core/Bootstrap.php';
\App\Core\Bootstrap::init();

// Optional: timezone
date_default_timezone_set('Africa/Cairo');

// -------------------------------
// CORS (dev) – allow localhost SPA with cookies
// -------------------------------
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Allow ANY http(s)://localhost:PORT or 127.0.0.1:PORT
$allowLocalhost = false;
if ($origin && preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin)) {
    $allowLocalhost = true;
}

if ($allowLocalhost) {
    // Browser SPA on localhost – allow cookies (sessions)
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
} else {
    // Tools like Postman (no Origin) or non-localhost – no cookies
    header('Access-Control-Allow-Origin: *');
}

// Always advertise methods + headers
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

// Handle preflight requests
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ------------------------------------
// Error handling
// ------------------------------------
set_exception_handler(function (Throwable $e) {
    $code = (int)$e->getCode();
    if ($code < 100 || $code > 599) {
        $code = 500;
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        ['error' => ['code' => $code, 'message' => $e->getMessage()]],
        JSON_UNESCAPED_UNICODE
    );
});

// Not Found: JSON for /api/*, HTML elsewhere
\Flight::map('notFound', function () {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_starts_with($uri, '/api/')) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['error' => ['code' => 404, 'message' => 'Not Found']],
            JSON_UNESCAPED_UNICODE
        );
    } else {
        \Flight::halt(
            404,
            '<h1>404 Not Found</h1><h3>The page you requested could not be found.</h3>'
        );
    }
});

// -------------------------------
// Flight view path (admin pages)
// -------------------------------
\Flight::set('flight.views.path', __DIR__ . '/../views');

// -------------------------------
// Routes: API + Admin
// -------------------------------
require_once __DIR__ . '/../routes/api.php';
require_once __DIR__ . '/../routes/admin.php';

// Optional: /docs -> /docs/ redirect (keeps nice URL)
\Flight::route('GET /docs', function () {
    header('Location: /docs/', true, 302);
});

// -------------------------------
// Run the app
// -------------------------------
\Flight::start();
