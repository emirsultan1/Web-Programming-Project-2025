<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class CategoriesDAO extends BaseDAO {
    public function __construct() {
        parent::__construct('categories', 'category_id');
    }

    public function createCategory(string $name): int {
        return $this->create(['name' => $name]);
    }

    public function updateCategory(int $id, array $fields): bool {
        unset($fields['category_id']); 
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    public function deleteCategory(int $id): bool {
        return $this->delete($id);
    }

    public function listCategories(int $limit = 100, int $offset = 0): array {
        return $this->findAll($limit, $offset);
    }
}
