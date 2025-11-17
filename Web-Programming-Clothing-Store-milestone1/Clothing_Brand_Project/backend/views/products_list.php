<?php
// $products is provided by the route
ob_start();
?>
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h1 class="h4 m-0">Products</h1>
    <a href="/api/v1/products" class="btn btn-sm btn-outline-secondary">View JSON</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category ID</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= (int)$p['product_id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars((string)($p['category_id'] ?? '—')) ?></td>
            <td><?= htmlspecialchars((string)$p['price']) ?></td>
            <td><?= htmlspecialchars((string)$p['stock_qty']) ?></td>
            <td><?= htmlspecialchars((string)($p['created_at'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Admin · Products';
include __DIR__ . '/layout.php';
