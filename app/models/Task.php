<?php
// app/models/Task.php
require_once __DIR__ . '/../../core/Model.php';

class Task extends Model
{
    public static function getByProject(int $projectId): array
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT * 
            FROM tasks
            WHERE project_id = :project_id
            ORDER BY `order` ASC, created_at ASC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Crea una tarea en una columna dada
     */
    
        /* ANTERIOR FIRMA
        public static function create(int $projectId, int $columnId, string $title, ?string $description, ?string $responsible, ?int $createdBy = null): int
        */
        public static function create(
                int $projectId,
                int $columnId,
                string $title,
                ?string $description,
                ?int $responsibleUserId,
                ?int $createdBy = null
                ): int

        {
            $db = self::db();

            $stmtOrder = $db->prepare("
                SELECT COALESCE(MAX(`order`), 0) + 1 AS next_order
                FROM tasks
                WHERE column_id = :column_id
            ");
            $stmtOrder->execute(['column_id' => $columnId]);
            $row = $stmtOrder->fetch();
            $nextOrder = $row ? (int)$row['next_order'] : 1;

            /*
                ANTERIOR INSERT INTO
            $stmt = $db->prepare("
                INSERT INTO tasks (project_id, column_id, title, responsible, description, `order`, created_by)
                VALUES (:project_id, :column_id, :title, :responsible, :description, :order, :created_by)
            ");

            */

            $stmt = $db->prepare("
                INSERT INTO tasks (project_id, column_id, title, description, `order`, created_by, responsible_user_id)
                VALUES (:project_id, :column_id, :title, :description, :order, :created_by, :responsible_user_id)
            ");


            /*
            ANTERIOR EXECUTE
            $stmt->execute([
                'project_id'  => $projectId,
                'column_id'   => $columnId,
                'title'       => $title,
                'responsible' => $responsible ?: null,
                'description' => $description,
                'order'       => $nextOrder,
                'created_by'  => $createdBy,
            ]);

            */
            
            $stmt->execute([
                'project_id'  => $projectId,
                'column_id'   => $columnId,
                'title'       => $title,
                'responsible_user_id' => $responsibleUserId,
                'description' => $description,
                'order'       => $nextOrder,
                'created_by'  => $createdBy,
            ]);

            // inserción de movimiento de la tarea para metricas

            $taskId = (int)$db->lastInsertId();

            $stmtMove = $db->prepare("
            INSERT INTO task_movements (task_id, project_id, from_column_id, to_column_id, moved_at, moved_by)
            VALUES (:task_id, :project_id, NULL, :to_column_id, NOW(), :moved_by)
            ");
            $stmtMove->execute([
            'task_id' => $taskId,
            'project_id' => $projectId,
            'to_column_id' => $columnId,
            'moved_by' => $createdBy
            ]);

            return $taskId;

        }


    /**
     * Mueve una tarea a otra columna.
     * Si la columna destino es "Hecho" (is_done = 1), marca completed_at.
     */
    public static function moveToColumn(int $taskId, int $newColumnId): void
    {
        $db = self::db();

        // Saber si la columna destino es de finalización
        $stmtCol = $db->prepare("
            SELECT is_done 
            FROM columns 
            WHERE id = :id
        ");
        $stmtCol->execute(['id' => $newColumnId]);
        $column = $stmtCol->fetch();

        if (!$column) {
            throw new Exception("Columna destino no encontrada");
        }

        $isDone = (int)$column['is_done'] === 1;
        $completedAt = $isDone ? date('Y-m-d H:i:s') : null;



        //obtenemos la columna actual para metricas
        $stmtOld = $db->prepare("SELECT project_id, column_id FROM tasks WHERE id = :id");
        $stmtOld->execute(['id' => $taskId]);
        $old = $stmtOld->fetch();
        if (!$old) throw new Exception("Tarea no encontrada");
        $projectId = (int)$old['project_id'];
        $fromColumnId = (int)$old['column_id'];



        $stmt = $db->prepare("
            UPDATE tasks
            SET column_id = :column_id,
                completed_at = :completed_at
            WHERE id = :id
        ");

        $stmt->execute([
            'column_id'    => $newColumnId,
            'completed_at' => $completedAt,
            'id'           => $taskId
        ]);

        // registramos el movimiento del update para metricas , luego del update
        $stmtMove = $db->prepare("
        INSERT INTO task_movements (task_id, project_id, from_column_id, to_column_id, moved_at)
        VALUES (:task_id, :project_id, :from_col, :to_col, NOW())
        ");
        $stmtMove->execute([
        'task_id' => $taskId,
        'project_id' => $projectId,
        'from_col' => $fromColumnId,
        'to_col' => $newColumnId
        ]);



    }

    public static function find(int $id): ?array
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();
        return $task ?: null;
    }

    public static function update(int $id, string $title, ?string $responsible, ?string $description): void
    {
        $db = self::db();
        $stmt = $db->prepare("
            UPDATE tasks
            SET title = :title,
                responsible = :responsible,
                description = :description
            WHERE id = :id
        ");

        $stmt->execute([
            'id'          => $id,
            'title'       => $title,
            'responsible' => $responsible ?: null,
            'description' => $description,
        ]);
    }


    public static function delete(int $id): void
    {
        $db = self::db();
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function reorderColumn(int $columnId, array $taskIds): void
    {
        $db = self::db();

        // Normalizar ids (enteros, únicos, en el orden recibido)
        $clean = [];
        foreach ($taskIds as $id) {
            $id = (int)$id;
            if ($id > 0 && !in_array($id, $clean, true)) {
                $clean[] = $id;
            }
        }

        if (empty($clean)) return;

        $stmt = $db->prepare("UPDATE tasks SET `order` = :ord WHERE id = :id AND column_id = :column_id");

        $ord = 1;
        foreach ($clean as $id) {
            $stmt->execute([
                'ord'       => $ord++,
                'id'        => $id,
                'column_id' => $columnId
            ]);
        }
    }

    public static function moveToColumnWithTx(int $taskId, int $newColumnId): void
    {
        // Reutilizamos la lógica existente de moveToColumn (completed_at, etc.)
        self::moveToColumn($taskId, $newColumnId);
    }

    public static function updateTitleResponsible(int $id, string $title, ?string $responsible): void
    {
        $db = self::db();
        $stmt = $db->prepare("
            UPDATE tasks
            SET title = :title,
                responsible = :responsible
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'responsible' => $responsible ?: null
        ]);
    }

    public static function countsByProjectIds(array $projectIds): array
    {
        $projectIds = array_values(array_filter(array_map('intval', $projectIds)));
        if (empty($projectIds)) return [];

        $db = self::db();

        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));

        $sql = "
            SELECT
            t.project_id,
            SUM(CASE WHEN c.is_done = 1 THEN 1 ELSE 0 END) AS done_count,
            SUM(CASE WHEN c.is_done = 0 AND LOWER(c.name) LIKE '%progreso%' THEN 1 ELSE 0 END) AS doing_count,
            SUM(CASE WHEN c.is_done = 0 AND LOWER(c.name) NOT LIKE '%progreso%' THEN 1 ELSE 0 END) AS todo_count
            FROM tasks t
            JOIN columns c ON c.id = t.column_id
            WHERE t.project_id IN ($placeholders)
            GROUP BY t.project_id
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($projectIds);

        $map = [];
        while ($row = $stmt->fetch()) {
            $pid = (int)$row['project_id'];
            $map[$pid] = [
                'todo'  => (int)$row['todo_count'],
                'doing' => (int)$row['doing_count'],
                'done'  => (int)$row['done_count'],
            ];
        }

        return $map;
    }






    
}
