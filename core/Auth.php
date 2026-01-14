<?php
// app/core/Auth.php
class Auth
{
    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function check(): bool
    {
        return self::userId() !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '?controller=auth&action=login');
            exit;
        }
    }

    public static function login(int $userId, string $role = 'member'): void
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
    }

    public static function role(): string
    {
        return $_SESSION['user_role'] ?? 'member';
    }

    public static function requireRole(array $allowed): void
    {
        $role = self::role();
        if (!in_array($role, $allowed, true)) {
            http_response_code(403);
            echo "No autorizado";
            exit;
        }
    }


    public static function logout(): void
    {
        session_destroy();
    }
}
