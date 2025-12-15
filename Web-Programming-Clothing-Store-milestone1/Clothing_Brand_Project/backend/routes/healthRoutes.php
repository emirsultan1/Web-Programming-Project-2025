<?php
declare(strict_types=1);

// Health check
\Flight::route('GET /api/v1/health', function () {
  \Flight::json([
    'status' => 'ok',
    'ts'     => date(DATE_ATOM),
  ]);
});

// Quiet favicon in dev
\Flight::route('GET /favicon.ico', function () {
  http_response_code(204);
});
