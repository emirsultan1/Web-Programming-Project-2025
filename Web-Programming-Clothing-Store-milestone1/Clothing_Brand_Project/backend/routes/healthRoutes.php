<?php
declare(strict_types=1);

// Health check
\Flight::route('GET /api/v1/health', function () {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'status' => 'ok',
    'ts'     => date(DATE_ATOM),
  ]);
});

// Quiet favicon in dev
\Flight::route('GET /favicon.ico', function () {
  http_response_code(204);
});
