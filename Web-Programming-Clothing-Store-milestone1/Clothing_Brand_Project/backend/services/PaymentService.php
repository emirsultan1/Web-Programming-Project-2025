<?php
namespace App\Services;

use App\Core\Validator;
use App\Core\Paginator;

/**
 * WHY this service exists:
 * - Validates order existence and business rules before creating/updating payments
 * - Ensures amounts/currencies/methods are sane
 * - Optionally updates the related order status when payment completes
 */
final class PaymentService {
  public function __construct(
    private \PaymentsDAO $dao,
    private \OrdersDAO $ordersDao
  ) {}

  public function list(array $q = []): array {
    [$limit,$offset] = Paginator::fromQuery($q);
    $items = $this->dao->findAll($limit,$offset);
    $total = $this->dao->countAll();
    return compact('items','total','limit','offset');
  }

  public function get(int $id): array {
    $row = $this->dao->findById($id);
    if (!$row) throw new \RuntimeException('Payment not found', 404);
    return $row;
  }

  /**
   * Create a payment
   * Expected payload:
   * {
   *   "order_id": 12,
   *   "payment_method": "credit_card" | "paypal" | "cash_on_delivery",
   *   "amount": 138.98,
   *   "currency": "USD",
   *   "status": "pending" | "completed" | "failed" | "refunded"   (optional; default 'pending')
   *   "transaction_id": "GW123...",                                  (optional)
   *   "metadata": {...}                                               (optional JSON object)
   * }
   *
   * Rules:
   * - order must exist
   * - amount must be > 0
   * - currency must be 3-letter uppercase
   * - allowed methods/status values only
   * - If status is 'completed' at creation time, update order.status -> 'paid'
   */
  public function create(array $data): array {
    Validator::requireFields($data, ['order_id', 'payment_method', 'amount']);

    // order exists?
    $order = $this->ordersDao->findById((int)$data['order_id']);
    if (!$order) throw new \RuntimeException('Order not found', 404);

    // validate fields
    $method = (string)$data['payment_method'];
    $allowedMethods = ['credit_card','paypal','cash_on_delivery'];
    if (!in_array($method, $allowedMethods, true)) {
      throw new \InvalidArgumentException('invalid payment_method', 422);
    }

    $amount = (float)$data['amount'];
    if ($amount <= 0) throw new \InvalidArgumentException('amount must be > 0', 422);

    $currency = strtoupper((string)($data['currency'] ?? 'USD'));
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
      throw new \InvalidArgumentException('currency must be 3-letter code', 422);
    }

    $status = (string)($data['status'] ?? 'pending');
    $allowedStatuses = ['pending','completed','failed','refunded'];
    if (!in_array($status, $allowedStatuses, true)) {
      throw new \InvalidArgumentException('invalid status', 422);
    }

    // OPTIONAL business rule: ensure amount matches order total (basic guard)
    // Comment this out if you want to allow partial payments.
    $orderTotal = (float)$order['total_amount'];
    if ($orderTotal > 0 && abs($amount - $orderTotal) > 0.00001) {
      throw new \InvalidArgumentException('amount must match order total', 422);
    }

    // prepare metadata
    $metadata = null;
    if (isset($data['metadata'])) {
      if (!is_array($data['metadata'])) {
        throw new \InvalidArgumentException('metadata must be JSON object', 422);
      }
      // store as JSON string; MySQL JSON will cast appropriately
      $metadata = json_encode($data['metadata'], JSON_UNESCAPED_UNICODE);
    }

    // create payment
    $paymentId = $this->dao->create([
      'order_id'       => (int)$data['order_id'],
      'payment_method' => $method,
      'amount'         => $amount,
      'currency'       => $currency,
      'status'         => $status,
      'transaction_id' => $data['transaction_id'] ?? null,
      'metadata'       => $metadata
    ]);

    // If completed on creation, mark order as paid
    if ($status === 'completed' && $order['status'] === 'pending') {
      $this->ordersDao->update((int)$data['order_id'], ['status' => 'paid']);
    }

    return $this->get((int)$paymentId);
  }

  /**
   * Update a payment (e.g., set status to completed/failed/refunded)
   * If status becomes 'completed' and order is still 'pending', set order->status='paid'.
   */
  public function update(int $id, array $data): array {
    if (isset($data['payment_method'])) {
      $allowed = ['credit_card','paypal','cash_on_delivery'];
      if (!in_array($data['payment_method'], $allowed, true)) {
        throw new \InvalidArgumentException('invalid payment_method', 422);
      }
    }
    if (isset($data['amount'])) {
      if ((float)$data['amount'] <= 0) throw new \InvalidArgumentException('amount must be > 0', 422);
    }
    if (isset($data['currency'])) {
      $data['currency'] = strtoupper((string)$data['currency']);
      if (!preg_match('/^[A-Z]{3}$/', $data['currency'])) {
        throw new \InvalidArgumentException('currency must be 3-letter code', 422);
      }
    }
    if (isset($data['status'])) {
      $allowedStatuses = ['pending','completed','failed','refunded'];
      if (!in_array($data['status'], $allowedStatuses, true)) {
        throw new \InvalidArgumentException('invalid status', 422);
      }
    }
    if (isset($data['metadata'])) {
      if (!is_array($data['metadata'])) {
        throw new \InvalidArgumentException('metadata must be JSON object', 422);
      }
      $data['metadata'] = json_encode($data['metadata'], JSON_UNESCAPED_UNICODE);
    }

    // Update
    $ok = $this->dao->update($id, $data);
    if (!$ok) throw new \RuntimeException('Payment not found', 404);

    // Side effect: if status now completed, mark order paid (if still pending)
    if (isset($data['status']) && $data['status'] === 'completed') {
      $p = $this->dao->findById($id);
      if ($p) {
        $order = $this->ordersDao->findById((int)$p['order_id']);
        if ($order && $order['status'] === 'pending') {
          $this->ordersDao->update((int)$order['order_id'], ['status' => 'paid']);
        }
      }
    }

    return $this->get($id);
  }

  public function delete(int $id): void {
    $ok = $this->dao->delete($id);
    if (!$ok) throw new \RuntimeException('Payment not found', 404);
  }
}
