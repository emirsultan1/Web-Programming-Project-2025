<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class OrdersDAO extends BaseDAO {
    public function __construct() {
        parent::__construct('orders', 'order_id');
    }

    public function createOrder(int $userId, string $status = 'pending', float $totalAmount = 0.0): int {
        return $this->create([
            'user_id' => $userId,
            'status' => $status,
            'total_amount' => $totalAmount
        ]);
    }

    public function updateOrder(int $id, array $fields): bool {
        unset($fields['order_id']);
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    public function deleteOrder(int $id): bool {
        return $this->delete($id);
    }

    public function listOrdersByUser(int $userId, int $limit = 100, int $offset = 0): array {
        $sql = "SELECT * FROM orders WHERE user_id = :uid ORDER BY order_id DESC LIMIT :lim OFFSET :off";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
