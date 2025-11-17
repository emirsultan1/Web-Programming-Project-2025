<?php
// -----------------------------
// Admin (Presentation Layer)
// -----------------------------

// Simple ping (you already tested this)
\Flight::route('GET /admin/ping', function () {
  header('Content-Type: text/plain; charset=utf-8');
  echo "admin ok";
});

// Views path check (you already tested this)
\Flight::route('GET /admin/debug/views', function () {
  $path = \Flight::get('flight.views.path');
  $tpl  = $path . DIRECTORY_SEPARATOR . 'products_list.php';
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'views_path' => $path,
    'exists_products_list' => file_exists($tpl),
    'template_checked' => $tpl
  ], JSON_UNESCAPED_UNICODE);
});

// === DIAGNOSTIC VERSION of /admin/products ===
// This will catch any exception and print it on the page so we can see what's wrong.
\Flight::route('GET /admin/products', function () {
  try {
    // 1) confirm ProductService is registered
    $svc = \Flight::get('ProductService');
    if (!$svc) {
      throw new RuntimeException('ProductService not found in Flight container. Check Bootstrap registration.');
    }

    // 2) fetch products (limit/offset plainly given)
    $out = $svc->list(['limit' => 200, 'offset' => 0]);

    // 3) extract items safely
    $products = is_array($out) ? ($out['items'] ?? []) : [];
    if (!is_array($products)) {
      throw new RuntimeException('Unexpected $products type. $out=' . json_encode($out));
    }

    // 4) render view
    \Flight::render('products_list.php', compact('products'));
  } catch (\Throwable $e) {
    // Show a friendly HTML error with details (only for local dev)
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<pre style="white-space:pre-wrap;padding:1rem;border:1px solid #ccc;background:#fff3f3">';
    echo "Admin page error:\n\n";
    echo htmlspecialchars($e->getMessage()) . "\n\n";
    echo htmlspecialchars($e->getTraceAsString());
    echo '</pre>';
  }
});
