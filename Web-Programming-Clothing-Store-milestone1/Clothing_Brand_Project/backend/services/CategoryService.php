<?php
namespace App\Services;

use App\Core\Validator;
use App\Core\Paginator;

/**
 * WHY this file exists:
 * - Keep category rules/validation in one place (business logic),
 * - So routes stay thin and DAOs stay focused on DB access.
 */
final class CategoryService {
  public function __construct(private \CategoriesDAO $dao) {}

  public function list(array $q = []): array {
    [$limit,$offset] = Paginator::fromQuery($q);
    $items = $this->dao->findAll($limit,$offset);
    $total = $this->dao->countAll();
    return compact('items','total','limit','offset');
  }

  public function get(int $id): array {
    $row = $this->dao->findById($id);
    if (!$row) throw new \RuntimeException('Category not found', 404);
    return $row;
  }

  public function create(array $data): array {
    Validator::requireFields($data, ['name']);
    if (trim((string)$data['name']) === '') throw new \InvalidArgumentException('name required', 422);
    $id = $this->dao->create(['name' => (string)$data['name']]);
    return $this->get((int)$id);
  }

  public function update(int $id, array $data): array {
    if (isset($data['name']) && trim((string)$data['name']) === '') {
      throw new \InvalidArgumentException('name required', 422);
    }
    $ok = $this->dao->update($id, $data);
    if (!$ok) throw new \RuntimeException('Category not found', 404);
    return $this->get($id);
  }

  public function delete(int $id): void {
    $ok = $this->dao->delete($id);
    if (!$ok) throw new \RuntimeException('Category not found', 404);
  }
}
