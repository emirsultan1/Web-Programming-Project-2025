<?php
namespace App\Services;

use App\Core\Validator;
use App\Core\Paginator;

/**
 * WHY this service exists:
 * - Centralizes user-specific rules (email format, role checks, password length)
 * - Keeps routes thin and DAOs focused on DB access only
 */
final class UserService {
  public function __construct(private \UsersDAO $dao) {}

  public function list(array $q = []): array {
    [$limit, $offset] = Paginator::fromQuery($q);
    $items = $this->dao->findAll($limit, $offset);
    $total = $this->dao->countAll();
    return compact('items','total','limit','offset');
  }

  public function get(int $id): array {
    $row = $this->dao->findById($id);
    if (!$row) throw new \RuntimeException('User not found', 404);
    return $row;
  }

  public function create(array $data): array {
    // required fields
    Validator::requireFields($data, ['name','email','password_hash']);

    // rules
    if (!filter_var((string)$data['email'], FILTER_VALIDATE_EMAIL)) {
      throw new \InvalidArgumentException('invalid email', 422);
    }
    if (strlen((string)$data['password_hash']) < 8) {
      throw new \InvalidArgumentException('password too short (>=8)', 422);
    }
    $role = $data['role'] ?? 'customer';
    if (!in_array($role, ['customer','admin'], true)) {
      throw new \InvalidArgumentException('invalid role', 422);
    }

    // create
    $id = $this->dao->create([
      'name' => (string)$data['name'],
      'email' => (string)$data['email'],
      'password_hash' => (string)$data['password_hash'],
      'role' => $role
    ]);

    return $this->get((int)$id);
  }

  public function update(int $id, array $data): array {
    if (isset($data['email']) && !filter_var((string)$data['email'], FILTER_VALIDATE_EMAIL)) {
      throw new \InvalidArgumentException('invalid email', 422);
    }
    if (isset($data['password_hash']) && strlen((string)$data['password_hash']) < 8) {
      throw new \InvalidArgumentException('password too short (>=8)', 422);
    }
    if (isset($data['role']) && !in_array($data['role'], ['customer','admin'], true)) {
      throw new \InvalidArgumentException('invalid role', 422);
    }

    $ok = $this->dao->update($id, $data);
    if (!$ok) throw new \RuntimeException('User not found', 404);
    return $this->get($id);
  }

  public function delete(int $id): void {
    $ok = $this->dao->delete($id);
    if (!$ok) throw new \RuntimeException('User not found', 404);
  }
}
