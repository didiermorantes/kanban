<?php
// app/views/layout.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kanban PHP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        header { margin-bottom: 20px; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }

        .kanban-board {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .kanban-column {
            background: #ffffff;
            border-radius: 6px;
            padding: 10px;
            flex: 1;
            min-width: 240px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* 🎨 Colores por tipo de columna */
        .kanban-column.todo {
            background: #fde2e2; /* rojo muy claro */
        }
        .kanban-column.in-progress {
            background: #fff9db; /* amarillo muy claro */
        }
        .kanban-column.done {
            background: #e2f7e2; /* verde muy claro */
        }

        .kanban-column h3 {
            margin-top: 0;
        }
        /* Tarjeta (tarea) estilo azul claro */
        .kanban-card {
        background: #dbeafe;               /* azul claro */
        border: 1px solid rgba(30,64,175,.18);
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        /* Un poco más "clickable" */
        .kanban-card:hover {
        box-shadow: 0 6px 14px rgba(0,0,0,0.10);
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            background: #eee;
        }
        .badge-success {
            background: #28a745;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th { background: #fafafa; text-align: left; }

        form {
            margin-top: 8px;
            margin-bottom: 8px;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            background: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .top-actions {
            margin-bottom: 10px;
        }

        /* 🪟 Modal para nueva tarea */

        /* Esta clase manda SIEMPRE */
        .hidden {
            display: none !important;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: flex;            /* visible cuando NO tiene .hidden */
            align-items: center;
            justify-content: center;
            z-index: 999;
        }
        .modal {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .modal-header h3 {
            margin: 0;
        }
        .modal-close {
            background: transparent;
            color: #333;
            font-size: 18px;
            cursor: pointer;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }



        .flash {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .flash-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }


        /* Drag & Drop
        
        
        .dragging {
            opacity: 0.6;
        }
        .droppable.drag-over {
            outline: 2px dashed rgba(0,0,0,0.25);
            outline-offset: 6px;
        }
        .tasks-container {
            min-height: 35px;
        }
            
        */



        /* =========================
        Drag & Drop PRO
        ========================= */

        /* Animación suave al mover */
        .kanban-card {
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        /* Tarjeta mientras se arrastra */
        .dragging {
            opacity: 0.5;
            box-shadow: 0 8px 16px rgba(0,0,0,0.25);
        }

        /* Columna destino */
        .droppable.drag-over {
            outline: 2px dashed rgba(0,0,0,0.35);
            outline-offset: 6px;
        }

        /* Contenedor */
        .tasks-container {
            min-height: 40px;
            
        }

        /* Placeholder (línea fantasma)
        .drop-placeholder {
            height: 38px;
            margin-bottom: 10px;
            border-radius: 4px;
            outline: 2px dashed rgba(255, 2, 2, 1);
            background: repeating-linear-gradient(
                45deg,
                rgba(0,0,0,0.08),
                rgba(0,0,0,0.08) 6px,
                rgba(0,0,0,0.02) 6px,
                rgba(0,0,0,0.02) 12px
            );
        }       
        
        */


        /* Evita que el placeholder “cambie de tamaño” y provoque saltos */
        .drop-placeholder {
        height: 50px;
        margin: 10px 0;
        border-radius: 6px;
        outline: 2px dashed rgba(255, 2, 2, 1);
        background: rgba(0,0,0,0.06);
        border: 2px dashed rgba(0,0,0,0.18);
        }

        /* Mejor feedback de columna activa */
        .droppable.drag-over {
        outline: 2px dashed rgba(0,0,0,0.35);
        outline-offset: 6px;
        }

        /* Más estable el drag */
        .dragging {
        opacity: 0.55;
        }



        /* Toasts */
        .toast-host {
        position: fixed;
        right: 16px;
        top: 16px;
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        }
        .toast {
        min-width: 220px;
        max-width: 320px;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 14px;
        box-shadow: 0 10px 18px rgba(0,0,0,0.18);
        transform: translateY(-6px);
        opacity: 0;
        transition: transform .18s ease, opacity .18s ease;
        }
        .toast-in { transform: translateY(0); opacity: 1; }
        .toast-out { transform: translateY(-6px); opacity: 0; }

        .toast-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .toast-error   { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .toast-info    { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }



        /* Mini stacked bars por tarea */
        .taskbar {
        height: 14px;
        border-radius: 999px;
        overflow: hidden;
        display: flex;
        background: rgba(0,0,0,0.06);
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08);
        min-width: 240px;
        }

        .taskbar-seg {
        height: 100%;
        position: relative;
        }

        .taskbar-seg:hover {
        filter: brightness(0.95);
        }

        .taskbar-legend {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin: 8px 0 12px;
        font-size: 12px;
        align-items: center;
        }

        .taskbar-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        }

        /* Tooltip simple */
        .taskbar-seg[data-tip]:hover::after {
        content: attr(data-tip);
        position: absolute;
        left: 50%;
        top: -34px;
        transform: translateX(-50%);
        white-space: nowrap;
        background: rgba(0,0,0,0.82);
        color: #fff;
        padding: 6px 8px;
        border-radius: 8px;
        font-size: 12px;
        pointer-events: none;
        z-index: 20;
        }

        .taskbar-seg[data-tip]:hover::before {
        content: "";
        position: absolute;
        left: 50%;
        top: -10px;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: rgba(0,0,0,0.82);
        }


        /* borde rojo para tareas en progreso mayores a 72h */
        .taskbar.alert-stuck {
        box-shadow: 0 0 0 2px #ef4444, inset 0 0 0 1px rgba(0,0,0,0.08);
        }


        /* ESTILOS PARA QUE EL SELECT QUEDE COMPACTO
        Fila compacta para mover  */
        .move-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        }

        /* Label compacto */
        .move-row label {
        margin: 0;
        font-size: 12px;
        opacity: 0.85;
        white-space: nowrap;
        }

        /* Select compacto */
        .move-row select {
        flex: 1;
        padding: 4px 8px;
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.18);
        font-size: 12px;
        background: rgba(255,255,255,0.9);
        max-width: 160px;
        }

        /* Botón compacto (si tienes botón "Mover") */
        .move-row button {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 12px;
        }
        /* fin select compacto */


        /* Badge responsable */
        .badge-resp{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:3px 10px;
        border-radius:999px;
        font-size:12px;
        line-height:1;
        background: rgba(255,255,255,0.55);
        border: 1px solid rgba(0,0,0,0.12);
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        /* “Avatar” circular con inicial */
        .badge-resp .avatar{
        width:18px;
        height:18px;
        border-radius:50%;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-weight:700;
        font-size:11px;
        background: rgba(59,130,246,0.20);
        border: 1px solid rgba(59,130,246,0.25);
        }

        /* Texto */
        .badge-resp .name{
        white-space:nowrap;
        max-width: 180px;
        overflow:hidden;
        text-overflow:ellipsis;
        }

        /* Variante “sin responsable” */
        .badge-resp.muted{
        opacity:0.75;
        }
    /* fin Badge responsable */



    /* Barra superior de color */
    .kanban-card{
    position:relative; /* necesario para la barra */
    }

    .kanban-card::before{
    content:"";
    position:absolute;
    left:0;
    top:0;
    height:6px;
    width:100%;
    border-top-left-radius:10px;
    border-top-right-radius:10px;
    background: rgba(0,0,0,0.12); /* default */
    }

    /* Colores por estado */
    .kanban-card.bar-todo::before { background: #f87171; }  /* rojo claro */
    .kanban-card.bar-doing::before{ background: #fbbf24; }  /* amarillo */
    .kanban-card.bar-done::before { background: #34d399; }  /* verde */

    /* fin barra de color superior */

    /* ok como icono */
    .btn-icon{
    width: 30px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.18);
    background: rgba(255,255,255,0.9);

    cursor: pointer;

    /* ✅ IMPORTANTE: que el icono SIEMPRE sea visible */
    color: #111827 !important;
    font-size: 14px;
    line-height: 1;
    }

    .btn-icon:hover{
    filter: brightness(0.97);
    }

    /* fin ok como icono */


    /* Transición base del fondo (si no existe) */
.kanban-card{
  transition: background-color 180ms ease, box-shadow 180ms ease;
}

/* Animación “flash” del fondo */
.kanban-card.flash{
  animation: cardFlash 220ms ease-out;
}

/* Colores por estado (se aplican vía variable CSS) */
.kanban-card{
  --flash-color: rgba(0,0,0,0); /* default */
}

@keyframes cardFlash{
  0%   { box-shadow: 0 0 0 0 var(--flash-color); }
  60%  { box-shadow: 0 0 0 6px var(--flash-color); }
  100% { box-shadow: 0 0 0 0 var(--flash-color); }
}

 /* fin Transición base del fondo (si no existe) */



 /* Base azul claro (ya lo tienes) */
.kanban-card{
  background: #dbeafe;
  transition: background-color 200ms ease, box-shadow 200ms ease;
}

/* Tintes por columna */
.kanban-card.bg-todo{
  background: linear-gradient(
    0deg,
    rgba(248,113,113,0.22),
    rgba(248,113,113,0.22)
  ), #dbeafe;
}

.kanban-card.bg-doing{
  background: linear-gradient(
    0deg,
    rgba(251,191,36,0.28),
    rgba(251,191,36,0.28)
  ), #dbeafe;
}

.kanban-card.bg-done{
  background: linear-gradient(
    0deg,
    rgba(52,211,153,0.30),
    rgba(52,211,153,0.30)
  ), #dbeafe;
}


/* Botones de acción (lista de proyectos) */
.btn-action {
  width: 32px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  border: 1px solid rgba(0,0,0,0.18);
  background: rgba(255,255,255,0.95);
  color: #111827;
  cursor: pointer;
  font-size: 14px;
  line-height: 1;
  margin-right: 4px;
}

.btn-action:hover {
  filter: brightness(0.96);
}

/* Variantes semánticas */
.btn-action.view { background: #e0f2fe; }   /* azul suave */
.btn-action.edit { background: #fef3c7; }   /* amarillo suave */
.btn-action.del  { background: #fee2e2; }   /* rojo suave */

.btn-action.del:hover {
  background: #fecaca;
}

/* fin Botones de acción (lista de proyectos) */

/* Progress bar (lista de proyectos) */
.progress-wrap{
  width: 180px;
  height: 10px;
  border-radius: 999px;
  background: rgba(0,0,0,0.08);
  overflow: hidden;
  box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08);
}

.progress-bar{
  height: 100%;
  width: 0%;
  border-radius: 999px;
  transition: width 250ms ease;
}

/* Colores según avance */
.progress-red{ background: #f87171; }
.progress-yellow{ background: #fbbf24; }
.progress-green{ background: #34d399; }

/* Texto compacto al lado */
.progress-text{
  font-size: 12px;
  margin-left: 8px;
  opacity: 0.85;
  white-space: nowrap;
}
/* fin Progress bar (lista de proyectos) */


/* Chips de conteo */
.chips {
  display: inline-flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  line-height: 1;
  border: 1px solid rgba(0,0,0,0.10);
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  background: rgba(255,255,255,0.7);
}

.chip .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

/* Colores */
.chip.todo  { background: rgba(248,113,113,0.20); }
.chip.todo  .dot { background: #f87171; }

.chip.doing { background: rgba(251,191,36,0.22); }
.chip.doing .dot { background: #fbbf24; }

.chip.done  { background: rgba(52,211,153,0.22); }
.chip.done  .dot { background: #34d399; }

/* FIN Chips de conteo */


/* Tooltip pro reutilizable (data-tip) */
.tip {
  position: relative;
}

/* Caja tooltip */
.tip[data-tip]:hover::after {
  content: attr(data-tip);
  position: absolute;
  left: 50%;
  bottom: calc(100% + 10px);
  transform: translateX(-50%);
  white-space: nowrap;

  background: rgba(0,0,0,0.82);
  color: #fff;
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 12px;
  line-height: 1.2;
  z-index: 999;

  opacity: 0;
  animation: tipIn 140ms ease-out forwards;
}

/* Flechita */
.tip[data-tip]:hover::before {
  content: "";
  position: absolute;
  left: 50%;
  bottom: calc(100% + 2px);
  transform: translateX(-50%);
  border: 6px solid transparent;
  border-top-color: rgba(0,0,0,0.82);
  z-index: 999;
  opacity: 0;
  animation: tipIn 140ms ease-out forwards;
}

@keyframes tipIn {
  from { opacity: 0; transform: translateX(-50%) translateY(2px); }
  to   { opacity: 1; transform: translateX(-50%) translateY(0); }
}
/* fin Tooltip pro reutilizable (data-tip) */


/* Grid responsive de proyectos */
.projects-grid{
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 14px;
  margin-top: 12px;
}

.project-card{
  background: rgba(255,255,255,0.95);
  border: 1px solid rgba(0,0,0,0.10);
  border-radius: 14px;
  padding: 14px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  transition: transform 140ms ease, box-shadow 140ms ease;
}

.project-card:hover{
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.10);
}

/* Header card */
.project-card .pc-head{
  display:flex;
  align-items:flex-start;
  justify-content: space-between;
  gap: 10px;
}

.project-card .pc-title{
  font-size: 16px;
  font-weight: 800;
  margin: 0;
}

.project-card .pc-sub{
  margin-top: 4px;
  font-size: 12px;
  opacity: 0.75;
}

/* Acciones */
.pc-actions{
  display:flex;
  gap: 6px;
  flex-wrap: nowrap;
  white-space: nowrap;
}

/* Secciones internas */
.pc-section{
  margin-top: 12px;
}

.pc-row{
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
}

/* fin Grid responsive de proyectos */

        
    </style>
</head>
<body>
<header>
    <h1>Tablero Kanban</h1>
    <nav>
        <a href="<?= BASE_URL ?>?controller=projects&action=index">Proyectos</a>
    </nav>
    <hr>
</header>

<main>
    <?php if (!empty($flash)): ?>
        <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php include $viewFile; ?>
</main>


<div id="toastHost" class="toast-host"></div>

<script>
window.showToast = function(type, msg) {
  const host = document.getElementById('toastHost');
  if (!host) return;

  const el = document.createElement('div');
  el.className = `toast toast-${type || 'info'}`;
  el.textContent = msg;

  host.appendChild(el);

  requestAnimationFrame(() => el.classList.add('toast-in'));

  setTimeout(() => {
    el.classList.remove('toast-in');
    el.classList.add('toast-out');
    setTimeout(() => el.remove(), 250);
  }, 2200);
}
</script>



</body>
</html>
