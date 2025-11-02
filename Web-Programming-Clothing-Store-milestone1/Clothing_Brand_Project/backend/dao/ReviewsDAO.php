<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class ReviewsDAO extends BaseDAO {
    public function __construct() {
        parent::__construct('reviews', 'review_id');
    }

    public function createReview(int $userId, int $productId, int $rating, ?string $comment = null): int {
        return $this->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'rating' => $rating,
            'comment' => $comment
        ]);
    }

    public function updateReview(int $id, array $fields): bool {
        unset($fields['review_id'], $fields['user_id'], $fields['product_id']);
        if (empty($fields)) return true;
        return $this->update($id, $fields);
    }

    public function deleteReview(int $id): bool {
        return $this->delete($id);
    }

    public function listReviewsForProduct(int $productId, int $limit = 100, int $offset = 0): array {
        $sql = "SELECT * FROM reviews WHERE product_id = :pid ORDER BY review_id DESC LIMIT :lim OFFSET :off";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':pid', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
