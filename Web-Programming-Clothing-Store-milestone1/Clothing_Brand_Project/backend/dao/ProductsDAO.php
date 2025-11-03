<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class ProductsDAO extends BaseDAO {
    public function __construct() {
        parent::__construct('products', 'product_id');
    }

    public function createProduct(string $name, ?string $description, float $price, int $stockQty, ?int $categoryId): int {
        return $this->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock_qty' => $stockQty,
            'category_id' => $categoryId
        ]);
    }

    public function updateProduct(int $id, array $fields): bool {
        unset($fields['product_id']);
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    public function deleteProduct(int $id): bool {
        return $this->delete($id);
    }

    public function listProducts(?int $categoryId = null, int $limit = 100, int $offset = 0): array {
        if ($categoryId === null) return $this->findAll($limit, $offset);

        $sql = "SELECT * FROM products WHERE category_id = :cid ORDER BY product_id DESC LIMIT :lim OFFSET :off";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
