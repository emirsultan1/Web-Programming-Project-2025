<?php
declare(strict_types=1);

// -----------------------------
// Order Items
// -----------------------------

\Flight::route('GET /api/v1/order-items', function () {
  $svc = \Flight::get('OrderItemService');
  $q   = \Flight::request()->query->data ?? [];
  $out = $svc->list($q); // should support order_id filter inside service

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/order-items', function () {
  $svc  = \Flight::get('OrderItemService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);

  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/order-items/@id:[0-9]+', function ($id) {
  $svc  = \Flight::get('OrderItemService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $updated = $svc->update((int)$id, $data);

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($updated, JSON_UNESCAPED_UNICODE);
});

\Flight::route('DELETE /api/v1/order-items/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('OrderItemService');
  $svc->delete((int)$id);
  http_response_code(204);
});
