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
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'item_price' => $itemPrice,
            'size' => $size,
            'color' => $color
        ]);
    }

    /** Get all items for a specific order */
    public function getItemsByOrder(int $orderId): array {
        $sql = "SELECT * FROM order_items WHERE order_id = :oid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Update a specific order item */
    public function updateItem(int $id, array $fields): bool {
        unset($fields['order_item_id']);
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    /** Delete a specific order item */
    public function deleteItem(int $id): bool {
        return $this->delete($id);
    }
}
