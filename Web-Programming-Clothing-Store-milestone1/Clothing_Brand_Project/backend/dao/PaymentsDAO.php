<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class PaymentsDAO extends BaseDAO {
    public function __construct() {
        parent::__construct('payments', 'payment_id');
    }

    /** Create new payment */
    public function createPayment(
        int $orderId,
        string $method,
        float $amount,
        string $currency = 'USD',
        string $status = 'pending',
        ?string $transactionId = null
    ): int {
        return $this->create([
            'order_id' => $orderId,
            'payment_method' => $method,
            'amount' => $amount,
            'currency' => $currency,
            'status' => $status,
            'transaction_id' => $transactionId
        ]);
    }

    /** Get payments by order */
    public function getPaymentsByOrder(int $orderId): array {
        $sql = "SELECT * FROM payments WHERE order_id = :oid ORDER BY payment_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Update payment (status, amount, etc.) */
    public function updatePayment(int $id, array $fields): bool {
        unset($fields['payment_id']);
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    /** Delete payment */
    public function deletePayment(int $id): bool {
        return $this->delete($id);
    }
}
