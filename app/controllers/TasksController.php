<?php
// app/controllers/TasksController.php
require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/ProjectMember.php';


class TasksController extends BaseController
{
    // POST <?= BASE_URL ?controller=tasks&action=store
    public function store(): void
    {
            Auth::requireLogin();

            $projectId = (int)$_POST['project_id'];
            ProjectMember::ensureMember($projectId, Auth::userId());
            
        $projectId   = (int)($_POST['project_id'] ?? 0);
        $columnId    = (int)($_POST['column_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $responsible = trim($_POST['responsible'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($projectId <= 0 || $columnId <= 0) {
            throw new Exception("Proyecto o columna no válidos");
        }

        if ($title === '') {
            throw new Exception("El título de la tarea es obligatorio");
        }

        $responsibleUserId = (int)($_POST['responsible_user_id'] ?? 0);
        if ($responsibleUserId <= 0) $responsibleUserId = null;

        Task::create($projectId, $columnId, $title, $description, $responsibleUserId, Auth::userId());


        /* ANTERIOR TASK CREATE
        Task::create($projectId, $columnId, $title, $description, $responsible, Auth::userId());
        */

        header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
        exit;
    }

    // POST <?= BASE_URL ?controller=tasks&action=move
    public function move(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $taskId    = (int)($_POST['task_id'] ?? 0);
        $columnId  = (int)($_POST['column_id'] ?? 0);

        if ($projectId <= 0 || $taskId <= 0 || $columnId <= 0) {
            $this->setFlash('error', 'Datos inválidos para mover la tarea.');
            header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
            exit;
        }

        // Mover
        Task::moveToColumn($taskId, $columnId);

        // Reordenar columna destino (la manda al final)
        $tasks = Task::getByProject($projectId);
        $ids = array_map(fn($t) => $t['id'],
            array_filter($tasks, fn($t) => $t['column_id'] == $columnId)
        );
        Task::reorderColumn($columnId, $ids);

        $this->setFlash('success', 'Tarea movida correctamente.');
        header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
        exit;
    }


        // GET ?controller=tasks&action=edit&id=XX&project_id=YY
    public function edit(): void
    {
        $taskId    = (int)($_GET['id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);

        if ($taskId <= 0 || $projectId <= 0) {
            throw new Exception("Datos inválidos para editar tarea");
        }

        $task = Task::find($taskId);
        if (!$task) {
            throw new Exception("Tarea no encontrada");
        }

        $this->render('tasks/edit', [
            'task'      => $task,
            'projectId' => $projectId,
        ]);
    }

    // POST ?controller=tasks&action=update
    public function update(): void
    {
        $taskId    = (int)($_POST['id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $responsible = trim($_POST['responsible'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($taskId <= 0 || $projectId <= 0 || $title === '') {
            $this->setFlash('error', 'Datos inválidos para actualizar la tarea.');
            header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
            exit;
        }

        Task::update($taskId, $title, $responsible, $description);

        $this->setFlash('success', 'Tarea actualizada correctamente.');

        header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
        exit;
    }

    // POST ?controller=tasks&action=destroy
    public function destroy(): void
    {
        $taskId    = (int)($_POST['id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);

        if ($taskId <= 0 || $projectId <= 0) {
            $this->setFlash('error', 'Datos inválidos para eliminar la tarea.');
            header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
            exit;
        }

        Task::delete($taskId);
        $this->setFlash('success', 'Tarea eliminada correctamente.');

        header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
        exit;
    }

    // POST < BASE_URL >?controller=tasks&action=moveAjax
    public function moveAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Aceptar distintos nombres de campo (compatibilidad)
            $projectId = (int)($_POST['project_id'] ?? 0);
            $taskId    = (int)($_POST['task_id'] ?? 0);

            // Puede venir como to_column_id (nuevo) o column_id (viejo)
            $toColumn  = (int)($_POST['to_column_id'] ?? ($_POST['column_id'] ?? 0));
            $fromColumn = (int)($_POST['from_column_id'] ?? 0);

            // Ordenes (opcionales)
            $toOrderCsv   = trim($_POST['to_order'] ?? '');
            $fromOrderCsv = trim($_POST['from_order'] ?? '');

            if ($projectId <= 0 || $taskId <= 0 || $toColumn <= 0) {
                throw new Exception("Datos insuficientes para mover/reordenar");
            }

            $toIds   = ($toOrderCsv === '') ? [] : array_filter(explode(',', $toOrderCsv));
            $fromIds = ($fromOrderCsv === '') ? [] : array_filter(explode(',', $fromOrderCsv));

            $db = Database::getConnection();
            $db->beginTransaction();

            // 1) mover tarea (maneja completed_at si cae en Hecho)
            Task::moveToColumn($taskId, $toColumn);

            // 2) reordenar destino si nos enviaron orden
            if (!empty($toIds)) {
                Task::reorderColumn($toColumn, $toIds);
            }

            // 3) reordenar origen si nos enviaron orden y tenemos columna
            if ($fromColumn > 0 && !empty($fromIds)) {
                Task::reorderColumn($fromColumn, $fromIds);
            }

            $db->commit();

            echo json_encode(['ok' => true]);
            exit;

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function updateInlineAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $taskId = (int)($_POST['task_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $responsible = trim($_POST['responsible'] ?? '');

            if ($taskId <= 0 || $title === '') {
                throw new Exception("Datos insuficientes para actualizar");
            }

            Task::updateTitleResponsible($taskId, $title, $responsible);

            echo json_encode([
                'ok' => true,
                'title' => $title,
                'responsible' => $responsible
            ]);
            exit;

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }


}
