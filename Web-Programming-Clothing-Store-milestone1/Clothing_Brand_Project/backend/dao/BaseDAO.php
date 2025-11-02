<?php
declare(strict_types=1);

require_once __DIR__ . '/../db/db.php';

/**
 * BaseDAO - shared Data Access Object layer for all entities.
 * Handles common CRUD logic with PDO prepared statements.
 */
abstract class BaseDAO {
    protected PDO $pdo;
    protected string $table;
    protected string $idCol;

    public function __construct(string $table, string $idCol = 'id') {
        $db = new Database();
        $this->pdo = $db->connect();  // connect to DB
        $this->table = $table;        // e.g. "users"
        $this->idCol = $idCol;        // e.g. "user_id"
    }

    /** 🔹 Create (INSERT) */
    public function create(array $data): int {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ":$c", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int)$this->pdo->lastInsertId();
    }

    /** 🔹 Read (SELECT by ID) */
    public function findById(int $id): ?array {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->idCol}` = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** 🔹 Read all (SELECT all rows) */
    public function findAll(int $limit = 100, int $offset = 0): array {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY `{$this->idCol}` DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 🔹 Update (by ID) */
    public function update(int $id, array $data): bool {
        $set = implode(', ', array_map(fn($c) => "`$c` = :$c", array_keys($data)));
        $sql = "UPDATE `{$this->table}` SET $set WHERE `{$this->idCol}` = :id";

        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    /** 🔹 Delete (by ID) */
    public function delete(int $id): bool {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->idCol}` = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
