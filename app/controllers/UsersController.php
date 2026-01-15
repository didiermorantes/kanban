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
}
