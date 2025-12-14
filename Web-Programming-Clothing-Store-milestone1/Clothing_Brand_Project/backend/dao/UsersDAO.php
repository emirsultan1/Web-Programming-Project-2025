<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class UsersDAO extends BaseDAO
{
    public function __construct()
    {
        parent::__construct('users', 'user_id');
    }

    /**
     * Create a new user, returns new user_id
     */
    public function createUser(string $name, string $email, string $passwordHash, string $role = 'customer'): int
    {
        return $this->create([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'role'          => $role,
        ]);
    }

    /**
     * Find a user by email, or null if not found
     */
    public function findByEmail(string $email): ?array
    {
        $sql  = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Check if an email is already used by any user
     */
    public function isEmailTaken(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    /**
     * Update arbitrary fields for a user_id (e.g., name, role, password_hash)
     */
    public function updateUser(int $userId, array $fields): bool
    {
        // prevent primary key changes
        unset($fields['user_id']);

        if (empty($fields)) {
            return true;
        }

        return $this->update($userId, $fields);
    }

    /**
     * Delete a user by id
     */
    public function deleteUser(int $userId): bool
    {
        return $this->delete($userId);
    }

    /**
     * Optional: list users (paged)
     */
    public function listUsers(int $limit = 100, int $offset = 0): array
    {
        return $this->findAll($limit, $offset);
    }
}
