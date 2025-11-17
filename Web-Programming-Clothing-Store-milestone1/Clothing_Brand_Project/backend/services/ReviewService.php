<?php
namespace App\Services;

use App\Core\Validator;
use App\Core\Paginator;

/**
 * WHY this service exists:
 * - Validates (user_id, product_id, rating) before touching DB
 * - Enforces rating 1..5 and that user/product exist
 * - Surfaces clean errors (404/422/409) instead of raw DB errors
 */
final class ReviewService {
  public function __construct(
    private \ReviewsDAO $dao,
    private \UsersDAO $usersDao,
    private \ProductsDAO $productsDao
  ) {}

  public function list(array $q = []): array {
    [$limit,$offset] = Paginator::fromQuery($q);
    // simple list (could be extended with filters later)
    $items = $this->dao->findAll($limit,$offset);
    $total = $this->dao->countAll();
    return compact('items','total','limit','offset');
  }

  public function get(int $id): array {
    $row = $this->dao->findById($id);
    if (!$row) throw new \RuntimeException('Review not found', 404);
    return $row;
  }

  /**
   * Expected payload:
   * {
   *   "user_id": 11,
   *   "product_id": 3,
   *   "rating": 5,
   *   "comment": "Great hoodie!"
   * }
   * - Checks user/product exist
   * - rating must be 1..5
   * - honors unique(user_id, product_id) — returns 409 if duplicate
   */
  public function create(array $data): array {
    Validator::requireFields($data, ['user_id','product_id','rating']);

    $user = $this->usersDao->findById((int)$data['user_id']);
    if (!$user) throw new \RuntimeException('User not found', 404);

    $product = $this->productsDao->findById((int)$data['product_id']);
    if (!$product) throw new \RuntimeException('Product not found', 404);

    $rating = (int)$data['rating'];
    if ($rating < 1 || $rating > 5) {
      throw new \InvalidArgumentException('rating must be between 1 and 5', 422);
    }

    try {
      $id = $this->dao->create([
        'user_id'    => (int)$data['user_id'],
        'product_id' => (int)$data['product_id'],
        'rating'     => $rating,
        'comment'    => isset($data['comment']) ? (string)$data['comment'] : null
      ]);
    } catch (\PDOException $e) {
      // handle duplicate review (unique user_id+product_id)
      if ($e->getCode() === '23000') {
        throw new \InvalidArgumentException('review already exists for this user and product', 409);
      }
      throw $e;
    }

    return $this->get((int)$id);
  }

  /**
   * Update rating/comment for a review.
   * - Keeps user_id/product_id immutable here.
   */
  public function update(int $id, array $data): array {
    if (isset($data['rating'])) {
      $r = (int)$data['rating'];
      if ($r < 1 || $r > 5) {
        throw new \InvalidArgumentException('rating must be between 1 and 5', 422);
      }
    }
    // prevent changing identity pair in this endpoint
    unset($data['user_id'], $data['product_id']);

    $ok = $this->dao->update($id, $data);
    if (!$ok) throw new \RuntimeException('Review not found', 404);

    return $this->get($id);
  }

  public function delete(int $id): void {
    $ok = $this->dao->delete($id);
    if (!$ok) throw new \RuntimeException('Review not found', 404);
  }
}
