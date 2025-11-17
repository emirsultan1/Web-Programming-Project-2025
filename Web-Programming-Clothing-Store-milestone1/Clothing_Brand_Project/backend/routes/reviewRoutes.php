<?php
declare(strict_types=1);

// -----------------------------
// Reviews
// -----------------------------

\Flight::route('GET /api/v1/reviews', function () {
  $svc = \Flight::get('ReviewService');
  $q   = \Flight::request()->query->data ?? [];
  $out = $svc->list($q); // e.g. filter by product_id in service

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/reviews/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('ReviewService');

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/reviews', function () {
  $svc  = \Flight::get('ReviewService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);

  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/reviews/@id:[0-9]+', function ($id) {
  $svc  = \Flight::get('ReviewService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $updated = $svc->update((int)$id, $data);

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($updated, JSON_UNESCAPED_UNICODE);
});

\Flight::route('DELETE /api/v1/reviews/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('ReviewService');
  $svc->delete((int)$id);
  http_response_code(204);
});
