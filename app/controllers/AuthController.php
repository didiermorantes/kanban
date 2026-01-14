<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../core/Auth.php';

class AuthController extends BaseController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';

            $user = User::findByEmail($email);
            if ($user && password_verify($pass, $user['password_hash'])) {
                Auth::login((int)$user['id'], $user['role'] ?? 'member');
                header('Location: ' . BASE_URL . '?controller=projects&action=index');
                exit;
            }

            $this->render('auth/login', ['error' => 'Credenciales inválidas.']);
            return;
        }

        $this->render('auth/login');
    }

    public function logout()
    {
        Auth::logout();
        header('Location: ' . BASE_URL . '?controller=auth&action=login');
        exit;
    }
}
