<?php
namespace App\Services;

use App\Core\Validator;
use App\Core\Paginator;

/**
 * Business logic for managing products
 */
final class ProductService {
  private \ProductsDAO $dao;

  public function __construct(\ProductsDAO $dao) {
    $this->dao = $dao;
  }

  public function list(array $query = []): array {
    [$limit, $offset] = Paginator::fromQuery($query);
    $categoryId = isset($query['category_id']) ? (int)$query['category_id'] : null;
    $items = $this->dao->listProducts($categoryId, $limit, $offset);
    $total = $this->dao->countAll($categoryId);
    return [
      'items' => $items,
      'total' => $total,
      'limit' => $limit,
      'offset' => $offset
    ];
  }

  public function get(int $id): array {
    $row = $this->dao->findById($id);
    if (!$row) {
      throw new \RuntimeException('Product not found', 404);
    }
    return $row;
  }

  public function create(array $data): array {
    Validator::requireFields($data, ['name','price','stock_qty']);
    Validator::nonNegativeNumber($data['price'], 'price');
    Validator::nonNegativeInt($data['stock_qty'], 'stock_qty');

    $id = $this->dao->createProduct(
      (string)$data['name'],
      $data['description'] ?? null,
      (float)$data['price'],
      (int)$data['stock_qty'],
      isset($data['category_id']) ? (int)$data['category_id'] : null
    );
    return $this->get((int)$id);
  }

  public function update(int $id, array $data): array {
    if (isset($data['price']))     Validator::nonNegativeNumber($data['price'], 'price');
    if (isset($data['stock_qty'])) Validator::nonNegativeInt($data['stock_qty'], 'stock_qty');

    $ok = $this->dao->updateProduct($id, $data);
    if (!$ok) throw new \RuntimeException('Product not found', 404);
    return $this->get($id);
  }

  public function delete(int $id): void {
    $ok = $this->dao->deleteProduct($id);
    if (!$ok) throw new \RuntimeException('Product not found', 404);
  }
}
