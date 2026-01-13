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

    public static function login(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
    }

    public static function logout(): void
    {
        session_destroy();
    }
}
