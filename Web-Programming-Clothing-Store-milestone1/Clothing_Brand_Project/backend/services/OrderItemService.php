<?php
namespace App\Services;

use App\Core\Paginator;
use App\Core\Validator;

/**
 * WHY this service exists:
 * - Validates order/product and quantity before touching DB
 * - Captures a price snapshot (item_price) from the product at time of adding
 * - Keeps the parent order's total_amount in sync on create/update/delete
 */
final class OrderItemService {
  public function __construct(
    private \OrderItemsDAO $dao,
    private \OrdersDAO $ordersDao,
    private \ProductsDAO $productsDao
  ) {}

  public function list(array $q = []): array {
    [$limit,$offset] = Paginator::fromQuery($q);

    if (!empty($q['order_id'])) {
      $orderId = (int)$q['order_id'];
      $items = $this->dao->listByOrderId($orderId, $limit, $offset);
      // For filtered lists, "total" can be a simple count of returned items (or you can add a countByOrderId if needed)
      $total = count($items);
      return compact('items','total','limit','offset');
    }

    $items = $this->dao->findAll($limit,$offset);
    $total = $this->dao->countAll();
    return compact('items','total','limit','offset');
  }

  public function get(int $id): array {
    $row = $this->dao->findById($id);
    if (!$row) throw new \RuntimeException('Order item not found', 404);
    return $row;
  }

  /**
   * Expected payload:
   * {
   *   "order_id": 7,
   *   "product_id": 3,
   *   "quantity": 2,
   *   "size": "M",           // optional
   *   "color": "Blue",       // optional
   *   "item_price": 49.99    // optional; if omitted we snapshot from products.price
   * }
   */
  public function create(array $data): array {
    Validator::requireFields($data, ['order_id', 'product_id', 'quantity']);

    // ensure order exists
    $order = $this->ordersDao->findById((int)$data['order_id']);
    if (!$order) throw new \RuntimeException('Order not found', 404);

    // ensure product exists
    $product = $this->productsDao->findById((int)$data['product_id']);
    if (!$product) throw new \RuntimeException('Product not found', 404);

    $qty = (int)$data['quantity'];
    if ($qty <= 0) throw new \InvalidArgumentException('quantity must be > 0', 422);

    // price snapshot
    $price = isset($data['item_price']) ? (float)$data['item_price'] : (float)$product['price'];
    if ($price <= 0) throw new \InvalidArgumentException('item_price must be > 0', 422);

    $id = $this->dao->create([
      'order_id'   => (int)$data['order_id'],
      'product_id' => (int)$data['product_id'],
      'size'       => $data['size']  ?? null,
      'color'      => $data['color'] ?? null,
      'quantity'   => $qty,
      'item_price' => $price,
    ]);

    // bump order total
    $line = $qty * $price;
    $newTotal = (float)$order['total_amount'] + $line;
    $this->ordersDao->update((int)$order['order_id'], ['total_amount' => $newTotal]);

    return $this->get((int)$id);
  }

  /**
   * Update an order item.
   * Allowed changes: quantity, size, color, item_price (re-snapshot or manual).
   * We adjust the parent order total by the delta of line totals.
   */
  public function update(int $id, array $data): array {
    $current = $this->dao->findById($id);
    if (!$current) throw new \RuntimeException('Order item not found', 404);

    $order = $this->ordersDao->findById((int)$current['order_id']);
    if (!$order) throw new \RuntimeException('Parent order not found', 500);

    // compute current line total
    $oldQty   = (int)$current['quantity'];
    $oldPrice = (float)$current['item_price'];
    $oldLine  = $oldQty * $oldPrice;

    // validate incoming
    if (isset($data['quantity'])) {
      $q = (int)$data['quantity'];
      if ($q <= 0) throw new \InvalidArgumentException('quantity must be > 0', 422);
    }
    if (isset($data['item_price'])) {
      $p = (float)$data['item_price'];
      if ($p <= 0) throw new \InvalidArgumentException('item_price must be > 0', 422);
    }

    // apply update
    $ok = $this->dao->update($id, $data);
    if (!$ok) throw new \RuntimeException('Order item not updated', 500);

    $updated = $this->dao->findById($id);
    // compute new line total
    $newQty   = (int)$updated['quantity'];
    $newPrice = (float)$updated['item_price'];
    $newLine  = $newQty * $newPrice;

    // adjust order total by delta
    $delta = $newLine - $oldLine;
    if (abs($delta) > 0.00001) {
      $newTotal = max(0, (float)$order['total_amount'] + $delta);
      $this->ordersDao->update((int)$order['order_id'], ['total_amount' => $newTotal]);
    }

    return $updated;
  }

  public function delete(int $id): void {
    $current = $this->dao->findById($id);
    if (!$current) throw new \RuntimeException('Order item not found', 404);

    $order = $this->ordersDao->findById((int)$current['order_id']);
    if (!$order) throw new \RuntimeException('Parent order not found', 500);

    // subtract line total
    $line = (int)$current['quantity'] * (float)$current['item_price'];
    $newTotal = max(0, (float)$order['total_amount'] - $line);

    $ok = $this->dao->delete($id);
    if (!$ok) throw new \RuntimeException('Order item not deleted', 500);

    $this->ordersDao->update((int)$order['order_id'], ['total_amount' => $newTotal]);
  }
}
