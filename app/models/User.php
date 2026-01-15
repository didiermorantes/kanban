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


    public static function countOwners(): int
    {
        $stmt = self::db()->query("SELECT COUNT(*) AS c FROM users WHERE role='owner'");
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    }

    public static function update(int $id, string $name, string $email, string $role): void
    {
        $allowed = ['owner','admin','member','viewer'];
        if (!in_array($role, $allowed, true)) $role = 'member';

        // email duplicado (excepto el mismo usuario)
        $stmt = self::db()->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            throw new Exception("Ya existe un usuario con ese email.");
        }

        $stmt = self::db()->prepare("
            UPDATE users
            SET name = :name, email = :email, role = :role
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role
        ]);
    }

    public static function updatePassword(int $id, string $plainPassword): void
    {
        if (trim($plainPassword) === '') return;

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = self::db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }




}
