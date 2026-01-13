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


}
