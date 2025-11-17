<?php
declare(strict_types=1);

// -----------------------------
// Users
// -----------------------------

\Flight::route('GET /api/v1/users', function () {
  $svc = \Flight::get('UserService');
  $q   = \Flight::request()->query->data ?? [];
  $out = $svc->list($q);

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/users/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('UserService');

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/users', function () {
  $svc  = \Flight::get('UserService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);

  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/users/@id:[0-9]+', function ($id) {
  $svc  = \Flight::get('UserService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $updated = $svc->update((int)$id, $data);

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($updated, JSON_UNESCAPED_UNICODE);
});

\Flight::route('DELETE /api/v1/users/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('UserService');
  $svc->delete((int)$id);
  http_response_code(204);
});
