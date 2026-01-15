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
        // $stmt = self::db()->prepare("SELECT id,name,email FROM users WHERE id=? LIMIT 1");
        $stmt = self::db()->prepare("SELECT id,name,email,role FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }


    public static function all(): array
    {
        $stmt = self::db()->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function create(string $name, string $email, string $plainPassword, string $role = 'member'): int
    {
        $allowed = ['owner','admin','member','viewer'];
        if (!in_array($role, $allowed, true)) $role = 'member';

        // evitar duplicado por email
        $existing = self::findByEmail($email);
        if ($existing) {
            throw new Exception("Ya existe un usuario con ese email.");
        }

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

        $stmt = self::db()->prepare("
            INSERT INTO users (name, email, password_hash, role)
            VALUES (:name, :email, :password_hash, :role)
        ");

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
            'role' => $role
        ]);

        return (int) self::db()->lastInsertId();
    }



}
