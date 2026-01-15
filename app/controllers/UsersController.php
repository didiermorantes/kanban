<?php
require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/User.php';

class UsersController extends BaseController
{
    public function index(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);

        $users = User::all();
        $this->render('users/index', [
            'users' => $users,
            'error' => $_GET['error'] ?? '',
            'ok'    => $_GET['ok'] ?? ''
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);

        $this->render('users/create', [
            'error' => $_GET['error'] ?? '',
            'ok'    => $_GET['ok'] ?? ''
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);

        $name  = trim($_POST['name'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $role  = trim($_POST['role'] ?? 'member');
        $pass  = $_POST['password'] ?? '';

        try {
            if ($name === '' || $email === '' || $pass === '') {
                throw new Exception("Nombre, email y contraseña son obligatorios.");
            }

            User::create($name, $email, $pass, $role);

            header('Location: ' . BASE_URL . '?controller=users&action=index&ok=' . urlencode('Usuario creado'));
            exit;
        } catch (Exception $e) {
            header('Location: ' . BASE_URL . '?controller=users&action=create&error=' . urlencode($e->getMessage()));
            exit;
        }
    }




    public function edit(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo "Usuario inválido"; exit; }

        $user = User::find($id);
        if (!$user) { http_response_code(404); echo "Usuario no encontrado"; exit; }

        $this->render('users/edit', [
            'user'  => $user,
            'error' => $_GET['error'] ?? '',
            'ok'    => $_GET['ok'] ?? ''
        ]);
    }

    public function update(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo "Usuario inválido"; exit; }

        $name  = trim($_POST['name'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $role  = trim($_POST['role'] ?? 'member');
        $pass  = trim($_POST['password'] ?? '');

        try {
            if ($name === '' || $email === '') {
                throw new Exception("Nombre y email son obligatorios.");
            }

            // No permitir quitar el último owner si estás cambiando rol
            $current = User::find($id);
            if ($current && ($current['role'] ?? '') === 'owner' && $role !== 'owner') {
                if (User::countOwners() <= 1) {
                    throw new Exception("No puedes quitar el rol owner al último owner del sistema.");
                }
            }

            User::update($id, $name, $email, $role);

            if ($pass !== '') {
                User::updatePassword($id, $pass);
            }

            header('Location: ' . BASE_URL . "?controller=users&action=edit&id=$id&ok=" . urlencode("Usuario actualizado"));
            exit;
        } catch (Exception $e) {
            header('Location: ' . BASE_URL . "?controller=users&action=edit&id=$id&error=" . urlencode($e->getMessage()));
            exit;
        }
    }

    public function destroy(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['owner','admin']);

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo "Usuario inválido"; exit; }

        // No auto-eliminarse
        if ($id === Auth::userId()) {
            http_response_code(400);
            echo "No puedes eliminar tu propio usuario.";
            exit;
        }

        $u = User::find($id);
        if (!$u) { http_response_code(404); echo "Usuario no encontrado"; exit; }

        // No eliminar último owner
        if (($u['role'] ?? '') === 'owner' && User::countOwners() <= 1) {
            http_response_code(400);
            echo "No puedes eliminar al último owner del sistema.";
            exit;
        }

        User::delete($id);

        header('Location: ' . BASE_URL . "?controller=users&action=index&ok=" . urlencode("Usuario eliminado"));
        exit;
    }



}
