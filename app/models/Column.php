<?php
// app/models/Column.php
require_once __DIR__ . '/../../core/Model.php';

class Column extends Model
{
    public static function getByProject(int $projectId): array
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT * 
            FROM columns
            WHERE project_id = :project_id
            ORDER BY position ASC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Crea las columnas por defecto para un proyecto nuevo
     */
    public static function createDefaultColumns(int $projectId): void
    {
        $db = self::db();

        $columns = [
            ['name' => 'Por hacer',   'position' => 1, 'is_done' => 0],
            ['name' => 'En progreso', 'position' => 2, 'is_done' => 0],
            ['name' => 'Hecho',       'position' => 3, 'is_done' => 1],
        ];

        $stmt = $db->prepare("
            INSERT INTO columns (project_id, name, position, is_done)
            VALUES (:project_id, :name, :position, :is_done)
        ");

        foreach ($columns as $col) {
            $stmt->execute([
                'project_id' => $projectId,
                'name'       => $col['name'],
                'position'   => $col['position'],
                'is_done'    => $col['is_done'],
            ]);
        }
    }
}
