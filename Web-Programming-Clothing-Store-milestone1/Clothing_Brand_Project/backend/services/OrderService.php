<?php
namespace App\Services;

use App\Core\Validator;
use App\Core\Paginator;

/**
 * WHY this service exists:
 * - Centralizes order rules: valid user, valid status, optional line-items, total calculation.
 * - Keeps routes thin and DAOs focused purely on DB access.
 */
final class OrderService {
  public function __construct(
    private \OrdersDAO $dao,
    private \OrderItemsDAO $itemsDao,
    private \ProductsDAO $productsDao,
    private \UsersDAO $usersDao
  ) {}

  /** List orders with pagination metadata */
  public function list(array $q = []): array {
    [$limit,$offset] = Paginator::fromQuery($q);
    $items = $this->dao->findAll($limit,$offset);
    $total = $this->dao->countAll();
    return compact('items','total','limit','offset');
  }

  /** Get an order by id (404 if missing) */
  public function get(int $id): array {
    $row = $this->dao->findById($id);
    if (!$row) throw new \RuntimeException('Order not found', 404);
    return $row;
  }

  /**
   * Create a new order.
   * Accepts payload:
   * {
   *   "user_id": 2,
   *   "items": [
   *     {"product_id":1,"quantity":2,"size":"M","color":"Blue"}
   *   ]
   * }
   * - Verifies user exists.
   * - If items provided: validates products, computes total, creates order_items, updates order total.
   */
  public function create(array $data): array {
    Validator::requireFields($data, ['user_id']);

    // Verify user exists
    $user = $this->usersDao->findById((int)$data['user_id']);
    if (!$user) throw new \RuntimeException('User not found', 404);

    // Create base order (pending, total 0 initially)
    $orderId = $this->dao->create([
      'user_id'      => (int)$data['user_id'],
      'total_amount' => 0,
      'status'       => 'pending'
    ]);

    // Optional items
    $total = 0.0;
    if (!empty($data['items']) && is_array($data['items'])) {
      foreach ($data['items'] as $it) {
        Validator::requireFields($it, ['product_id','quantity']);
        $product = $this->productsDao->findById((int)$it['product_id']);
        if (!$product) throw new \RuntimeException('Product not found: '.$it['product_id'], 404);

        $qty = (int)$it['quantity'];
        if ($qty < 1) throw new \InvalidArgumentException('quantity >= 1', 422);

        $price = (float)$product['price'];
        $this->itemsDao->create([
          'order_id'   => (int)$orderId,
          'product_id' => (int)$it['product_id'],
          'size'       => $it['size']  ?? null,
          'color'      => $it['color'] ?? null,
          'quantity'   => $qty,
          'item_price' => $price
        ]);
        $total += $price * $qty;
      }
    }

    // Update order total if we added items
    if ($total > 0) {
      $this->dao->update((int)$orderId, ['total_amount' => $total]);
    }

    return $this->get((int)$orderId);
  }

  /**
   * Update order fields.
   * - Validates status against allowed values.
   * - Could be extended later to prevent illegal transitions.
   */
  public function update(int $id, array $data): array {
    if (isset($data['status'])) {
      $allowed = ['pending','paid','shipped','delivered','cancelled'];
      if (!in_array($data['status'], $allowed, true)) {
        throw new \InvalidArgumentException('invalid status', 422);
      }
    }
    $ok = $this->dao->update($id, $data);
    if (!$ok) throw new \RuntimeException('Order not found', 404);
    return $this->get($id);
  }

  /** Delete order (cascade removes items via FK) */
  public function delete(int $id): void {
    $ok = $this->dao->delete($id);
    if (!$ok) throw new \RuntimeException('Order not found', 404);
  }
}
