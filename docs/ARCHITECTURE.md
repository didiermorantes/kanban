# Arquitectura del Sistema Kanban

Este proyecto implementa un sistema web tipo Kanban utilizando una arquitectura
Modelo–Vista–Controlador (MVC) con un Front Controller centralizado.

---

## Estructura general



kanban/
├── app/
│ ├── controllers/
│ ├── models/
│ ├── views/
│ └── core/
├── config/
├── docs/
├── public/
└── database/


---

## Front Controller

El archivo `public/index.php` actúa como punto único de entrada:

- Inicializa la sesión
- Carga configuración
- Resuelve controlador y acción
- Evita acceso directo a lógica interna

---

## Controladores

Responsables de:
- Validar permisos
- Orquestar flujo de la aplicación
- Invocar modelos
- Renderizar vistas

Ejemplo:
- `ProjectsController`
- `TasksController`
- `UsersController`

---

## Modelos

Encapsulan el acceso a datos:
- Uso exclusivo de PDO
- Consultas preparadas
- Métodos estáticos orientados a dominio

Ejemplos:
- `Project`
- `Task`
- `User`
- `ProjectMember`

---

## Vistas

- HTML + PHP
- Sin lógica de negocio
- Escape de salida (`htmlspecialchars`)
- CSS y JS nativo

---

## Seguridad

- Autenticación por sesión
- Roles globales y por proyecto
- Validación estricta de membresía
- Protección de endpoints AJAX

---

## Métricas y auditoría

- Registro de movimientos de tareas
- Tiempos por columna
- CFD y WIP
- Usuario responsable de cada movimiento