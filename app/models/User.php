<?php
require_once __DIR__ . '/../../config/db.php';

class User
{
    private static function db(): PDO { return Database::getConnection(); }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT id,name,email FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }
}
