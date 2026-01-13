<?php
// core/BaseController.php

abstract class BaseController
{
    protected function render(string $view, array $params = []): void
    {
        extract($params);
        $viewFile = __DIR__ . '/../app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("Vista no encontrada: $viewFile");
        }

        // Obtener mensaje flash una sola vez
        $flash = $this->getFlash();

        include __DIR__ . '/../app/views/layout.php';
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,   // 'success' | 'error' | 'info'
            'message' => $message
        ];
    }

    protected function getFlash(): ?array
    {
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']); // se consume
            return $flash;
        }
        return null;
    }
}
