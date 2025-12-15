<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class OrdersDAO extends BaseDAO {
    public function __construct() {
        // table: orders, primary key: order_id
        parent::__construct('orders', 'order_id');
    }

    // ---------------------------------------------
    // Existing convenience methods
    // ---------------------------------------------
    public function createOrder(int $userId, string $status = 'pending', float $totalAmount = 0.0): int {
        return $this->create([
            'user_id'      => $userId,
            'status'       => $status,
            'total_amount' => $totalAmount
        ]);
    }

    public function updateOrder(int $id, array $fields): bool {
        // Ensure we don't try to overwrite the PK
        unset($fields['order_id']);
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    public function deleteOrder(int $id): bool {
        return $this->delete($id);
    }

    public function listOrdersByUser(int $userId, int $limit = 100, int $offset = 0): array {
        $sql = "
            SELECT *
            FROM orders
            WHERE user_id = :uid
            ORDER BY order_id DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ---------------------------------------------
    // New methods for /api/v1/my-orders support
    // ---------------------------------------------

    /**
     * Used by OrderService::listByUser()
     * Returns paginated orders for a given user.
     */
    public function findAllByUser(int $userId, int $limit, int $offset): array {
        // You could call listOrdersByUser() directly, but we keep SQL explicit.
        $sql = "
            SELECT *
            FROM orders
            WHERE user_id = :uid
            ORDER BY order_id DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Count total orders for a given user (for pagination meta)
     */
    public function countAllByUser(int $userId): int {
        $sql = "SELECT COUNT(*) FROM orders WHERE user_id = :uid";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
