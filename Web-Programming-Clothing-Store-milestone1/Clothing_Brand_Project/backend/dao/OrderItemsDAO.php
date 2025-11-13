<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class OrderItemsDAO extends BaseDAO {
    public function __construct() {
        parent::__construct('order_items', 'order_item_id');
    }

    /** Create a new order item */
    public function createItem(
        int $orderId,
        int $productId,
        int $quantity,
        float $itemPrice,
        ?string $size = null,
        ?string $color = null
    ): int {
        return $this->create([
            'order_id'   => $orderId,
            'product_id' => $productId,
            'quantity'   => $quantity,
            'item_price' => $itemPrice,
            'size'       => $size,
            'color'      => $color
        ]);
    }

    /** Update a specific order item */
    public function updateItem(int $id, array $fields): bool {
        unset($fields['order_item_id'], $fields['order_id'], $fields['product_id']); // keep identity immutable via this helper
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    /** Delete a specific order item */
    public function deleteItem(int $id): bool {
        return $this->delete($id);
    }

    /** Legacy helper you already had (no pagination) */
    public function getItemsByOrder(int $orderId): array {
        $sql = "SELECT * FROM order_items WHERE order_id = :oid ORDER BY order_item_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Method expected by OrderItemService::list()
     * Same as above but with LIMIT/OFFSET for pagination
     */
    public function listByOrderId(int $orderId, int $limit = 100, int $offset = 0): array {
        $sql = "SELECT * FROM order_items
                WHERE order_id = :oid
                ORDER BY order_item_id DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':oid', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
