<?php
// -----------------------------
// Health check
// -----------------------------
\Flight::route('GET /api/v1/health', function () {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['status' => 'ok', 'ts' => date(DATE_ATOM)]);
});

// Quiet favicon in dev
\Flight::route('GET /favicon.ico', function () {
  http_response_code(204);
});

// -----------------------------
// Products
// -----------------------------
\Flight::route('GET /api/v1/products', function () {
  $svc = \Flight::get('ProductService');
  $q = \Flight::request()->query->data ?? [];
  $out = $svc->list($q);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/products/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('ProductService');
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/products', function () {
  $svc = \Flight::get('ProductService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/products/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('ProductService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $updated = $svc->update((int)$id, $data);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($updated, JSON_UNESCAPED_UNICODE);
});

\Flight::route('DELETE /api/v1/products/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('ProductService');
  $svc->delete((int)$id);
  http_response_code(204);
});

// -----------------------------
// Categories
// -----------------------------
\Flight::route('GET /api/v1/categories', function () {
  $svc = \Flight::get('CategoryService');
  $q = \Flight::request()->query->data ?? [];
  $out = $svc->list($q);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/categories/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('CategoryService');
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/categories', function () {
  $svc = \Flight::get('CategoryService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/categories/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('CategoryService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $updated = $svc->update((int)$id, $data);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($updated, JSON_UNESCAPED_UNICODE);
});

\Flight::route('DELETE /api/v1/categories/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('CategoryService');
  $svc->delete((int)$id);
  http_response_code(204);
});

// -----------------------------
// Users
// -----------------------------
\Flight::route('GET /api/v1/users', function () {
  $svc = \Flight::get('UserService');
  $q = \Flight::request()->query->data ?? [];
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
  $svc = \Flight::get('UserService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/users/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('UserService');
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

// -----------------------------
// Orders
// -----------------------------
\Flight::route('GET /api/v1/orders', function () {
  $svc = \Flight::get('OrderService');
  $q = \Flight::request()->query->data ?? [];
  $out = $svc->list($q);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/orders/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('OrderService');
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/orders', function () {
  $svc = \Flight::get('OrderService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/orders/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('OrderService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $updated = $svc->update((int)$id, $data);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($updated, JSON_UNESCAPED_UNICODE);
});

\Flight::route('DELETE /api/v1/orders/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('OrderService');
  $svc->delete((int)$id);
  http_response_code(204);
});

// -----------------------------
// Order Items
// -----------------------------
\Flight::route('GET /api/v1/order-items', function () {
  $svc = \Flight::get('OrderItemService');
  $q = \Flight::request()->query->data ?? [];
  $out = $svc->list($q);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/order-items/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('OrderItemService');
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/order-items', function () {
  $svc = \Flight::get('OrderItemService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/order-items/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('OrderItemService');
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

// -----------------------------
// Payments
// -----------------------------
\Flight::route('GET /api/v1/payments', function () {
  $svc = \Flight::get('PaymentService');
  $q = \Flight::request()->query->data ?? [];
  $out = $svc->list($q);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/payments/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('PaymentService');
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/payments', function () {
  $svc = \Flight::get('PaymentService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/payments/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('PaymentService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $updated = $svc->update((int)$id, $data);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($updated, JSON_UNESCAPED_UNICODE);
});

\Flight::route('DELETE /api/v1/payments/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('PaymentService');
  $svc->delete((int)$id);
  http_response_code(204);
});

// -----------------------------
// Reviews
// -----------------------------
\Flight::route('GET /api/v1/reviews', function () {
  $svc = \Flight::get('ReviewService');
  $q = \Flight::request()->query->data ?? [];
  $out = $svc->list($q);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
});

\Flight::route('GET /api/v1/reviews/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('ReviewService');
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($svc->get((int)$id), JSON_UNESCAPED_UNICODE);
});

\Flight::route('POST /api/v1/reviews', function () {
  $svc = \Flight::get('ReviewService');
  $data = json_decode(\Flight::request()->getBody(), true) ?? [];
  $created = $svc->create($data);
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(201);
  echo json_encode($created, JSON_UNESCAPED_UNICODE);
});

\Flight::route('PUT /api/v1/reviews/@id:[0-9]+', function ($id) {
  $svc = \Flight::get('ReviewService');
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
