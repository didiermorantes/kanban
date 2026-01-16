<?php
// app/models/Project.php
require_once __DIR__ . '/../../core/Model.php';

class Project extends Model
{
    public int $id;
    public string $name;
    public ?string $description;
    public string $created_at;

    /**
     * Obtiene todos los proyectos con su porcentaje de avance
     */

    public static function allWithProgress(): array
    {
        $db = self::db();

        $sql = "
            SELECT 
                p.*,
                COUNT(t.id) AS total_tasks,
                SUM(CASE WHEN c.is_done = 1 THEN 1 ELSE 0 END) AS done_tasks,
                CASE 
                    WHEN COUNT(t.id) = 0 THEN 0
                    ELSE ROUND(
                        (SUM(CASE WHEN c.is_done = 1 THEN 1 ELSE 0 END) / COUNT(t.id)) * 100,
                        2
                    )
                END AS progress_percentage,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(t.responsible), '') ORDER BY t.responsible SEPARATOR ', ') AS responsibles
            FROM projects p
            LEFT JOIN tasks t ON t.project_id = p.id
            LEFT JOIN columns c ON c.id = t.column_id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }





    /**
     * Busca un proyecto por id
     */
    public static function find(int $id): ?array
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();

        return $project ?: null;
    }

    /**
     * Crea un proyecto y devuelve su ID
     */

/* ANTERIOR CREATE 

    public static function create(string $name, ?string $description, ?string $responsible, ?int $createdBy): int
    {
        $db = self::db();

        $stmt = $db->prepare("
            INSERT INTO projects (created_by, name, responsible, description)
            VALUES (:created_by, :name, :responsible, :description)
        ");

        $stmt->execute([
            'created_by'  => $createdBy,
            'name'        => $name,
            'responsible' => $responsible ?: null,
            'description' => $description ?: null
        ]);

        return (int) $db->lastInsertId();
    }
*/

    public static function create(string $name, ?string $description, ?int $responsibleUserId, ?int $createdBy): int
{
    $db = self::db();
    $responsibleUserId = $responsibleUserId ?: null;
    $stmt = $db->prepare("
        INSERT INTO projects (created_by, name, responsible_user_id, description)
        VALUES (:created_by, :name, :responsible_user_id, :description)
    ");
    $stmt->execute([
        'created_by' => $createdBy,
        'name' => $name,
        'responsible_user_id' => $responsibleUserId,
        'description' => $description ?: null
    ]);
    return (int)$db->lastInsertId();
}




/*
ANTERIOR UPDATE
        public static function update(int $id, string $name, ?string $description): void
    {
        $db = self::db();
        $stmt = $db->prepare("
            UPDATE projects
            SET name = :name,
                description = :description
            WHERE id = :id
        ");

        $stmt->execute([
            'id'          => $id,
            'name'        => $name,
            'description' => $description,
        ]);
    }
*/

public static function update(int $id, string $name, ?string $description, ?int $responsibleUserId): void
{
    $db = self::db();
    $stmt = $db->prepare("
        UPDATE projects
        SET name = :name,
            description = :description,
            responsible_user_id = :responsible_user_id
        WHERE id = :id
    ");
    $stmt->execute([
        'id' => $id,
        'name' => $name,
        'description' => $description ?: null,
        'responsible_user_id' => $responsibleUserId
    ]);
}



    public static function delete(int $id): void
    {
        $db = self::db();
        $stmt = $db->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function allForUser(int $userId): array
{
    $db = self::db();

    $sql = "
        SELECT p.*
        FROM projects p
        JOIN project_members pm ON pm.project_id = p.id
        WHERE pm.user_id = ?
        ORDER BY p.id DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}


/*

ANTERIOR ALLWITHPROGRESSFORUSER

    public static function allWithProgressForUser(int $userId): array
    {
        $db = self::db();

        $sql = "
            SELECT 
                p.*,
                ru.name AS project_responsible_name,
                COUNT(t.id) AS total_tasks,
                SUM(CASE WHEN c.is_done = 1 THEN 1 ELSE 0 END) AS done_tasks,
                CASE 
                    WHEN COUNT(t.id) = 0 THEN 0
                    ELSE ROUND(
                        (SUM(CASE WHEN c.is_done = 1 THEN 1 ELSE 0 END) / COUNT(t.id)) * 100,
                        2
                    )
                END AS progress_percentage
            FROM projects p
            JOIN project_members pm ON pm.project_id = p.id
            LEFT JOIN tasks t ON t.project_id = p.id
            LEFT JOIN columns c ON c.id = t.column_id
            LEFT JOIN users ru ON ru.id = p.responsible_user_id
            WHERE pm.user_id = :user_id
            GROUP BY p.id
            ORDER BY p.id DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

 */   

    public static function allWithProgressForUser(
        int $userId,
        ?string $q = null,
        ?int $responsibleUserId = null,
        ?string $sort = null,
        bool $under50 = false
    ): array
    {
        $db = self::db();

        $where = " pm.user_id = :user_id ";

        /* PARA ORDENAMIENTO  POR AVANCE Y FILTRO POR ESTADO */
        $orderBy = "p.id DESC";
        if ($sort === 'progress_desc') $orderBy = "progress_percentage DESC, p.id DESC";
        if ($sort === 'progress_asc')  $orderBy = "progress_percentage ASC, p.id DESC";
        if ($sort === 'name_asc')      $orderBy = "p.name ASC, p.id DESC";
        if ($sort === 'name_desc')     $orderBy = "p.name DESC, p.id DESC";

        $having = "";
        if ($under50) {
            $having = " HAVING progress_percentage < 50 ";
        }

        /* FIN PARA ORDENAMIENTO  POR AVANCE Y FILTRO POR ESTADO */



        $params = ['user_id' => $userId];

        if ($q !== null && trim($q) !== '') {
            $where .= " AND (p.name LIKE :q OR p.description LIKE :q) ";
            $params['q'] = '%' . trim($q) . '%';
        }

        if ($responsibleUserId !== null && $responsibleUserId > 0) {
            $where .= " AND p.responsible_user_id = :rid ";
            $params['rid'] = $responsibleUserId;
        }






        $sql = "
            SELECT 
                p.*,
                ru.name AS project_responsible_name,
                COUNT(t.id) AS total_tasks,
                SUM(CASE WHEN c.is_done = 1 THEN 1 ELSE 0 END) AS done_tasks,
                CASE 
                    WHEN COUNT(t.id) = 0 THEN 0
                    ELSE ROUND(
                        (SUM(CASE WHEN c.is_done = 1 THEN 1 ELSE 0 END) / COUNT(t.id)) * 100,
                        2
                    )
                END AS progress_percentage
            FROM projects p
            JOIN project_members pm ON pm.project_id = p.id
            LEFT JOIN users ru ON ru.id = p.responsible_user_id
            LEFT JOIN tasks t ON t.project_id = p.id
            LEFT JOIN columns c ON c.id = t.column_id
            WHERE $where
            GROUP BY p.id
            $having
            ORDER BY $orderBy
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }



    public static function responsibleOptionsForUser(int $userId): array
    {
        $db = self::db();
        $sql = "
            SELECT DISTINCT u.id, u.name, u.email
            FROM projects p
            JOIN project_members pm ON pm.project_id = p.id
            JOIN users u ON u.id = p.responsible_user_id
            WHERE pm.user_id = :user_id
            ORDER BY u.name
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }



    
}
