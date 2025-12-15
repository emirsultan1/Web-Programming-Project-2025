<?php
declare(strict_types=1);

class AuthMiddleware
{
    /**
     * Ensure the PHP session is started.
     * (Cookie params are already set in your index.php before session_start)
     */
    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Ensure there is a logged-in user in the session.
     * Returns true if OK, false if blocked (and sends JSON error).
     */
    public static function requireLogin(): bool
    {
        self::ensureSession();

        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            \Flight::json([
                'success' => false,
                'errors'  => ['Authentication required.'],
            ], 401);
            return false;
        }

        return true;
    }

    /**
     * Ensure there is a logged-in user with a given role (e.g. 'admin').
     * Returns true if OK, false if blocked (and sends JSON error).
     */
    public static function requireRole(string $role): bool
    {
        if (!self::requireLogin()) {
            return false;
        }

        $user = $_SESSION['user'];

        if (($user['role'] ?? null) !== $role) {
            \Flight::json([
                'success' => false,
                'errors'  => ['Forbidden: insufficient permissions.'],
            ], 403);
            return false;
        }

        return true;
    }

    /**
     * Helper: get current user or null.
     */
    public static function currentUser(): ?array
    {
        self::ensureSession();
        return (isset($_SESSION['user']) && is_array($_SESSION['user'])) ? $_SESSION['user'] : null;
    }
}
