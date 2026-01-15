<?php
// app/controllers/ProjectsController.php
require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Column.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/KanbanMetrics.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/ProjectMember.php';
require_once __DIR__ . '/../models/User.php';



class ProjectsController extends BaseController
{
    public function index(): void
    {
        Auth::requireLogin();

        $userId = Auth::userId();

        $projects = Project::allWithProgressForUser($userId);
        // $this->render('projects/index', ['projects' => $projects]);

        $projectIds = array_map(fn($p) => (int)$p['id'], $projects);
        $taskCounts = Task::countsByProjectIds($projectIds);

        $this->render('projects/index', [
            'projects' => $projects,
            'taskCounts' => $taskCounts
        ]);



    }

    public function show(): void
    {

        Auth::requireLogin();

        $projectId = (int)($_GET['id'] ?? 0);
        ProjectMember::ensureMember($projectId, Auth::userId());


        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            throw new Exception("Proyecto no válido");
        }

        $project = Project::find($id);
        if (!$project) {
            throw new Exception("Proyecto no encontrado");
        }

        $columns = Column::getByProject($id);
        $tasks   = Task::getByProject($id);
        $members = ProjectMember::usersForProject($projectId);


        $tasksByColumn = [];
        foreach ($columns as $col) {
            $tasksByColumn[$col['id']] = [];
        }
        foreach ($tasks as $task) {
            $tasksByColumn[$task['column_id']][] = $task;
        }

        $this->render('projects/show', [
            'project'       => $project,
            'columns'       => $columns,
            'tasksByColumn' => $tasksByColumn
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);


        $this->render('projects/create', []);
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);


        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $responsibleUserId = (int)($_POST['responsible_user_id'] ?? 0);
        if ($responsibleUserId <= 0) $responsibleUserId = null;


        if ($name === '') {
            throw new Exception("El nombre del proyecto es obligatorio");
        }

        $userId = Auth::userId();

        $projectId = Project::create($name, $description, $responsibleUserId, $userId);


        // crear columnas por defecto
        Column::createDefaultColumns($projectId);

        // ✅ crear membresía owner (CLAVE para no ver “No autorizado”)
        ProjectMember::add($projectId, $userId, 'owner');

        header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
        exit;
    }


        // GET ?controller=projects&action=edit&id=1
    public function edit(): void
    {

        /* ANTERIOR EDIT

        Auth::requireLogin();

        ProjectMember::ensureMember($projectId, Auth::userId());


        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("Proyecto no válido");
        }

        $project = Project::find($id);
        if (!$project) {
            throw new Exception("Proyecto no encontrado");
        }

        $this->render('projects/edit', ['project' => $project]);

        */
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);


        $projectId = (int)($_GET['id'] ?? 0);
        ProjectMember::ensureMember($projectId, Auth::userId());

        $project = Project::find($projectId);
        $members = ProjectMember::usersForProject($projectId);

        $this->render('projects/edit', [
        'project' => $project,
        'members' => $members
        ]);


    }

    // POST ?controller=projects&action=update
    public function update(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);


        /* ANTERIOR UPDATE 

        ProjectMember::ensureMember($projectId, Auth::userId());


        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || $name === '') {
            $this->setFlash('error', 'Datos inválidos para actualizar el proyecto.');
            header('Location: ' . BASE_URL . '?controller=projects&action=index');
            exit;
        }

        Project::update($id, $name, $description);

        */

         // ✅ id SIEMPRE desde POST en update
        $projectId = (int)($_POST['id'] ?? 0);
        if ($projectId <= 0) {
            http_response_code(400);
            echo "Proyecto inválido";
            exit;
        }

        if ($name === '') {
            // Puedes manejarlo con flash/toast luego
            http_response_code(400);
            echo "El nombre es obligatorio";
            exit;
        }

        // ✅ permiso antes de guardar (no después)
        ProjectMember::ensureMember($projectId, Auth::userId());

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $responsibleUserId = (int)($_POST['responsible_user_id'] ?? 0);
        if ($responsibleUserId <= 0) $responsibleUserId = null;
        if ($responsibleUserId !== null) {
            $role = ProjectMember::roleFor($projectId, $responsibleUserId);
            if (!$role) {
                http_response_code(400);
                echo "Responsable inválido (no es miembro del proyecto)";
                exit;
            }
        }



        Project::update($projectId, $name, $description, $responsibleUserId);

        


        $this->setFlash('success', 'Proyecto actualizado correctamente.');

          // ✅ redirección con id y exit
        header('Location: ' . BASE_URL . '?controller=projects&action=show&id=' . $projectId);
        exit;
    }

    // POST ?controller=projects&action=destroy
    public function destroy(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);

        // ✅ tomar id (si eliminas por link <a>, viene por GET)
        // $projectId = (int)($_GET['id'] ?? 0);

        // Si usas formulario POST para eliminar, usa esto en vez de GET:
        $projectId = (int)($_POST['id'] ?? 0);

        if ($projectId <= 0) {
            http_response_code(400);
            echo "Proyecto inválido";
            $this->setFlash('error', 'Proyecto no válido para eliminar.');
            header('Location: ' . BASE_URL . '?controller=projects&action=index');
            exit;
        }

        ProjectMember::ensureMember($projectId, Auth::userId());


        /*
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Proyecto no válido para eliminar.');
            header('Location: ' . BASE_URL . '?controller=projects&action=index');
            exit;
        }
        */

        // ✅ eliminar
        Project::delete($projectId);
        $this->setFlash('success', 'Proyecto eliminado correctamente.');

        header('Location: ' . BASE_URL . '?controller=projects&action=index');
        exit;
    }

    public function report(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception("Proyecto no válido");

        $project = Project::find($id);
        if (!$project) throw new Exception("Proyecto no encontrado");

        $report = KanbanMetrics::projectReport($id);

        $this->render('projects/report', [
            'project' => $project,
            'report' => $report
        ]);
    }

    public function members(): void
    {
        Auth::requireLogin();

        $projectId = (int)($_GET['id'] ?? 0);
        if ($projectId <= 0) {
            http_response_code(400);
            echo "Proyecto inválido";
            exit;
        }

        // Cualquier miembro puede ver la lista
        ProjectMember::ensureMember($projectId, Auth::userId());

        $project = Project::find($projectId);
        $members = ProjectMember::usersForProject($projectId);

        // Rol del usuario actual en este proyecto (para la UI)
        $myRole = ProjectMember::roleFor($projectId, Auth::userId());

        $this->render('projects/members', [
            'project' => $project,
            'members' => $members,
            'myRole'  => $myRole,
            'error'   => $_GET['error'] ?? '',
            'ok'      => $_GET['ok'] ?? ''
        ]);
    }

    public function addMember(): void
    {
        Auth::requireLogin();

        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($projectId <= 0) {
            http_response_code(400);
            echo "Proyecto inválido";
            exit;
        }

        // Solo owner/admin del PROYECTO pueden gestionar miembros
        ProjectMember::ensureRole($projectId, Auth::userId(), ['owner','admin']);

        $email = trim($_POST['email'] ?? '');
        $role  = trim($_POST['role'] ?? 'member');

        try {
            ProjectMember::addByEmail($projectId, $email, $role);
            header('Location: ' . BASE_URL . "?controller=projects&action=members&id=$projectId&ok=Miembro agregado");
            exit;
        } catch (Exception $e) {
            $msg = urlencode($e->getMessage());
            header('Location: ' . BASE_URL . "?controller=projects&action=members&id=$projectId&error=$msg");
            exit;
        }
    }

    public function updateMemberRole(): void
    {
        Auth::requireLogin();

        $projectId = (int)($_POST['project_id'] ?? 0);
        $userId    = (int)($_POST['user_id'] ?? 0);
        $newRole   = trim($_POST['role'] ?? '');

        if ($projectId <= 0 || $userId <= 0) {
            http_response_code(400);
            echo "Datos inválidos";
            exit;
        }

        ProjectMember::ensureRole($projectId, Auth::userId(), ['owner','admin']);

        try {
            // No permitir quitar el último owner
            $currentRole = ProjectMember::roleFor($projectId, $userId);
            if ($currentRole === 'owner' && $newRole !== 'owner') {
                if (ProjectMember::countOwners($projectId) <= 1) {
                    throw new Exception("No puedes quitar el rol de owner al último propietario del proyecto.");
                }
            }

            ProjectMember::updateRole($projectId, $userId, $newRole);
            header('Location: ' . BASE_URL . "?controller=projects&action=members&id=$projectId&ok=Rol actualizado");
            exit;
        } catch (Exception $e) {
            $msg = urlencode($e->getMessage());
            header('Location: ' . BASE_URL . "?controller=projects&action=members&id=$projectId&error=$msg");
            exit;
        }
    }


    public function removeMember(): void
    {
        Auth::requireLogin();

        $projectId = (int)($_POST['project_id'] ?? 0);
        $userId    = (int)($_POST['user_id'] ?? 0);

        if ($projectId <= 0 || $userId <= 0) {
            http_response_code(400);
            echo "Datos inválidos";
            exit;
        }

        ProjectMember::ensureRole($projectId, Auth::userId(), ['owner','admin']);

        try {
            $currentRole = ProjectMember::roleFor($projectId, $userId);

            if ($currentRole === 'owner' && ProjectMember::countOwners($projectId) <= 1) {
                throw new Exception("No puedes eliminar al último owner del proyecto.");
            }

            // Evitar auto-borrado accidental (opcional)
            if ($userId === Auth::userId()) {
                throw new Exception("No puedes eliminarte a ti mismo desde aquí.");
            }

            ProjectMember::remove($projectId, $userId);

            header('Location: ' . BASE_URL . "?controller=projects&action=members&id=$projectId&ok=Miembro eliminado");
            exit;
        } catch (Exception $e) {
            $msg = urlencode($e->getMessage());
            header('Location: ' . BASE_URL . "?controller=projects&action=members&id=$projectId&error=$msg");
            exit;
        }
    }



}
