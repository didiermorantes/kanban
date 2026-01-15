<?php
require_once __DIR__ . '/../../config/db.php';

class ProjectMember
{
    private static function db(): PDO { return Database::getConnection(); }

    public static function roleFor(int $projectId, int $userId): ?string
    {
        $stmt = self::db()->prepare("SELECT role FROM project_members WHERE project_id=? AND user_id=? LIMIT 1");
        $stmt->execute([$projectId, $userId]);
        $row = $stmt->fetch();
        return $row ? $row['role'] : null;
    }

    public static function ensureMember(int $projectId, int $userId): void
    {
        $role = self::roleFor($projectId, $userId);
        if (!$role) {
            http_response_code(403);
            echo "No autorizado";
            exit;
        }
    }

    public static function add(int $projectId, int $userId, string $role='member'): void
    {
        $stmt = self::db()->prepare("INSERT IGNORE INTO project_members(project_id,user_id,role) VALUES(?,?,?)");
        $stmt->execute([$projectId, $userId, $role]);
    }

    public static function usersForProject(int $projectId): array
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email, pm.role
            FROM project_members pm
            JOIN users u ON u.id = pm.user_id
            WHERE pm.project_id = ?
            ORDER BY FIELD(pm.role,'owner','admin','member','viewer'), u.name
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public static function ensureRole(int $projectId, int $userId, array $allowedRoles): void
    {
        $role = self::roleFor($projectId, $userId);
        if (!$role || !in_array($role, $allowedRoles, true)) {
            http_response_code(403);
            echo "No autorizado";
            exit;
        }
    }

    public static function addByEmail(int $projectId, string $email, string $role = 'member'): void
    {
        $email = trim(strtolower($email));
        if ($email === '') {
            throw new Exception("Email inválido");
        }

        $db = self::db();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if (!$u) {
            throw new Exception("No existe un usuario registrado con ese email");
        }

        $userId = (int)$u['id'];
        self::add($projectId, $userId, $role); // ya lo tienes (INSERT IGNORE)
    }

    public static function updateRole(int $projectId, int $userId, string $role): void
    {
        $allowed = ['owner','admin','member','viewer'];
        if (!in_array($role, $allowed, true)) {
            throw new Exception("Rol inválido");
        }

        $db = self::db();
        $stmt = $db->prepare("UPDATE project_members SET role = ? WHERE project_id = ? AND user_id = ?");
        $stmt->execute([$role, $projectId, $userId]);
    }

    public static function remove(int $projectId, int $userId): void
    {
        $db = self::db();
        $stmt = $db->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
    }

    public static function countOwners(int $projectId): int
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT COUNT(*) AS c FROM project_members WHERE project_id = ? AND role = 'owner'");
        $stmt->execute([$projectId]);
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    }



}
