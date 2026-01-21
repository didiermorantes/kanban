# 🧩 Sistema Web Kanban – Gestión de Proyectos y Tareas

Sistema web tipo **Kanban** para la gestión colaborativa de proyectos y tareas, desarrollado en **PHP bajo arquitectura MVC**, con control de roles, métricas avanzadas y experiencia de usuario inspirada en herramientas profesionales como Trello o Jira.

---

## 🚀 Características principales

### 🔐 Autenticación y roles
- Sistema de login con usuarios registrados.
- Roles globales:
  - `owner`
  - `admin`
  - `member`
  - `viewer`
- Control de acceso por rol y por proyecto.
- Cierre de sesión seguro.

---

### 👤 Gestión de usuarios
- Crear, editar y eliminar usuarios.
- Asignación de roles globales.
- Protección contra:
  - eliminación del último owner.
  - auto-eliminación del usuario autenticado.
- Contraseñas cifradas con `password_hash`.

---

### 📁 Gestión de proyectos
- Crear, editar y eliminar proyectos.
- Asignación de **responsable del proyecto** (usuario real).
- Tabla de proyectos con:
  - responsable
  - porcentaje de avance
  - métricas de tareas
- **Filtros avanzados**:
  - búsqueda por palabra clave
  - filtro por responsable
  - filtro por avance < 50%
  - ordenamiento por avance o nombre

---

### 👥 Miembros del proyecto
- Asignación de usuarios a proyectos.
- Roles por proyecto:
  - owner / admin / member / viewer
- Validación estricta de permisos antes de cualquier acción.
- Solo miembros pueden ver o modificar un proyecto.

---

### 🧩 Tablero Kanban
- Visualización por columnas.
- Tarjetas de tareas con:
  - título
  - responsable
  - colores dinámicos por estado
- **Drag & Drop refinado**:
  - sin parpadeos
  - actualización visual inmediata
  - persistencia mediante AJAX

---

### 📝 Gestión de tareas
- Crear, editar y eliminar tareas.
- Asignar responsables (usuarios miembros del proyecto).
- Reordenamiento dentro de columnas.
- Movimiento entre columnas con historial.

---

### 📊 Métricas y reportes
- **Cumulative Flow Diagram (CFD)**.
- Tiempo total por columna.
- Tiempo por tarea.
- Línea WIP (Work In Progress).
- Detección visual de tareas bloqueadas o con exceso de tiempo.
- Auditoría completa de movimientos (`task_movements`).

---

### 🎨 Experiencia de usuario
- Botones con jerarquía visual:
  - primarios
  - secundarios
  - destructivos
- Acciones alineadas y consistentes.
- Badges de responsables.
- Microanimaciones suaves.
- Interfaz clara y responsive.

---

## 🧱 Arquitectura y stack

- **Backend:** PHP (Programación Orientada a Objetos)
- **Arquitectura:** MVC + Front Controller
- **Base de datos:** MySQL
- **Frontend:** HTML, CSS, JavaScript nativo
- **Seguridad:** sesiones, control de roles, validación de membresía por proyecto

---

## ⚙️ Instalación (entorno local)

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/didiermorantes/kanban.git
2. Configurar la base de datos en:

  config/db.php

3. Importar el esquema SQL en MySQL.

4. Configurar el proyecto en un servidor local (XAMPP, Laragon, etc.).

5. Acceder desde el navegador:

  http://localhost/kanban/public

📌 Posibles evoluciones futuras

Invitaciones por email.

Registro por token.

Exportación de métricas.

Notificaciones.

Temas visuales.

API REST.

👨‍💻 Autor

Didier Morantes
Ingeniero Electrónico – Especialista en Ingeniería de Software

