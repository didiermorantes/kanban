<?php
// app/views/projects/show.php
?>
<h2>Proyecto: <?= htmlspecialchars($project['name']) ?></h2>
<p><?= nl2br(htmlspecialchars($project['description'] ?? '')) ?></p>

<p>
    <a href="<?= BASE_URL ?>?controller=projects&action=index" class="btn-secondary">
        ← Volver al listado de proyectos
    </a>
</p>

<div class="top-actions">
    <button id="openTaskModal">+ Nueva tarea</button>
</div>

<a href="<?= BASE_URL ?>?controller=projects&action=report&id=<?= (int)$project['id'] ?>">
  <button class="btn-secondary">📊 Ver métricas</button>
</a>


<div class="kanban-board" id="kanbanBoard" data-project-id="<?= (int)$project['id'] ?>">
    <?php foreach ($columns as $column): ?>
        <?php
            // Asignar clase según el tipo de columna
            $columnClass = 'kanban-column';
            if (!empty($column['is_done']) && (int)$column['is_done'] === 1) {
                $columnClass .= ' done';
            } elseif (stripos($column['name'], 'progreso') !== false) {
                $columnClass .= ' in-progress';
            } else {
                $columnClass .= ' todo';
            }

            $colId = $column['id'];
            $tasks = $tasksByColumn[$colId] ?? [];
        ?>

        <div class="<?= $columnClass ?> droppable"
          data-column-id="<?= (int)$column['id'] ?>"
          data-column-name="<?= htmlspecialchars($column['name']) ?>">

            <h3><?= htmlspecialchars($column['name']) ?></h3>

            <div class="tasks-container">
                <?php if (empty($tasks)): ?>
                    <p class="empty-col"><em>Sin tareas en esta columna.</em></p>
                <?php else: ?>

                  <?php
                    $colNameById = [];
                    foreach ($columns as $c) $colNameById[(int)$c['id']] = $c['name'];
                  ?>



                    <?php foreach ($tasks as $task): ?>
                              <?php
                                $colName = mb_strtolower(trim($colNameById[(int)$task['column_id']] ?? ''));
                                $barClass = 'bar-todo';
                                if (strpos($colName, 'progreso') !== false) $barClass = 'bar-doing';
                                if (strpos($colName, 'hecho') !== false || strpos($colName, 'done') !== false) $barClass = 'bar-done';
                              ?>
                              <div class="kanban-card draggable <?= $barClass ?>"
                                  id="task-<?= (int)$task['id'] ?>"
                                  draggable="true"
                                  data-task-id="<?= (int)$task['id'] ?>"
                                  data-current-column-id="<?= (int)$task['column_id'] ?>">

                             <!-- titulo editable -->
                            <strong class="task-title editable tip" data-field="title" data-tip="Doble Click para Editar Título">
                            <?= htmlspecialchars($task['title']) ?>
                            </strong>

                            <!-- responsable -->
                            <?php if (!empty($task['responsible'])): ?>
                                <div style="margin-top:4px;">
                                      <?php
                                        $resp = trim($task['responsible'] ?? '');
                                        $initial = $resp !== '' ? mb_strtoupper(mb_substr($resp, 0, 1)) : '—';
                                      ?>
                                      <div style="margin-top:6px;">
                                        <span class="badge-resp task-responsible editable <?= $resp === '' ? 'muted' : '' ?> tip"
                                              data-field="responsible"
                                              data-tip="Doble Click para Editar Responsable">
                                          <span class="avatar"><?= htmlspecialchars($initial) ?></span>
                                          <span class="name"><?= htmlspecialchars($resp !== '' ? $resp : 'Sin responsable') ?></span>
                                        </span>
                                      </div>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($task['description'])): ?>
                                <small><?= nl2br(htmlspecialchars($task['description'])) ?></small><br>
                            <?php endif; ?>

                            <!-- Mantengo el mover por select como “plan B” -->
                                <form method="POST" action="<?= BASE_URL ?>?controller=tasks&action=move">
                                  <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">
                                  <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">

                                  <div class="move-row">
                                    <label for="move-<?= (int)$task['id'] ?>">Mover a</label>

                                    <select id="move-<?= (int)$task['id'] ?>" name="column_id">
                                      <?php foreach ($columns as $col): ?>
                                        <option value="<?= (int)$col['id'] ?>" <?= ((int)$col['id'] === (int)$task['column_id']) ? 'selected' : '' ?>>
                                          <?= htmlspecialchars($col['name']) ?>
                                        </option>
                                      <?php endforeach; ?>
                                    </select>

                                    <button type="submit" class="btn-icon tip" data-tip="Mover tarea">
                                      ✔
                                    </button>

                                  </div>
                                </form>

                            <!-- Mantengo el mover por select como “plan B” -->

                            <div style="margin-top:5px;">
                                <a href="<?= BASE_URL ?>?controller=tasks&action=edit&id=<?= (int)$task['id'] ?>&project_id=<?= (int)$project['id'] ?>"
                                  class="btn-action edit tip"
                                  data-tip="Editar tarea"
                                >
                                     ✏️
                                </a>
                                |
                                <form method="post"
                                      action="<?= BASE_URL ?>?controller=tasks&action=destroy"
                                      style="display:inline"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                                    <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                                    <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">
                                    <button class="btn-action del tip" data-tip="Eliminar Tarea" type="submit" class="btn-secondary">🗑️</button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 🪟 Modal para crear nueva tarea -->
<div id="taskModalOverlay" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <h3>Nueva tarea</h3>
            <button type="button" class="modal-close" id="closeTaskModal">&times;</button>
        </div>

        <form method="post" action="<?= BASE_URL ?>?controller=tasks&action=store">
            <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">

            <label for="column_id">Columna</label>
            <select id="column_id" name="column_id" required>
                <option value="">Selecciona una columna</option>
                <?php foreach ($columns as $colOption): ?>
                    <option value="<?= $colOption['id'] ?>">
                        <?= htmlspecialchars($colOption['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="title">Título de la tarea *</label>
            <input type="text" id="title" name="title" placeholder="Ej: Definir requerimientos" required>

            <label for="responsible">Responsable (opcional)</label>
            <input type="text" id="responsible" name="responsible" placeholder="Ej: Didier Morantes">


            <label for="description">Descripción (opcional)</label>
            <textarea id="description" name="description" rows="3" placeholder="Detalles de la tarea..."></textarea>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelTaskModal">Cancelar</button>
                <button type="submit">Crear tarea</button>
            </div>
        </form>
    </div>
</div>

<script>
// =========================
// Modal (igual que antes)
// =========================
document.addEventListener('DOMContentLoaded', function () {
    const openBtn   = document.getElementById('openTaskModal');
    const overlay   = document.getElementById('taskModalOverlay');
    const closeBtn  = document.getElementById('closeTaskModal');
    const cancelBtn = document.getElementById('cancelTaskModal');

    function openModal() { overlay.classList.remove('hidden'); }
    function closeModal() { overlay.classList.add('hidden'); }

    if (openBtn)   openBtn.addEventListener('click', openModal);
    if (closeBtn)  closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });





});

// =========================
// Drag & Drop estilo Trello
// =========================

document.addEventListener('DOMContentLoaded', function () {
  const board = document.getElementById('kanbanBoard');
  if (!board) return;

  const projectId = board.getAttribute('data-project-id');

  let draggedCard = null;
  let placeholder = document.createElement('div');
  placeholder.className = 'drop-placeholder';

  // Para evitar parpadeo: track de enter/leave por columna
  const enterCounter = new Map();

  function getTaskIds(container) {
    return Array.from(container.querySelectorAll('.draggable'))
      .map(el => el.getAttribute('data-task-id'));
  }

  function getAfterElement(container, y) {
    const els = [...container.querySelectorAll('.draggable:not(.dragging)')];
    let closest = { offset: Number.NEGATIVE_INFINITY, element: null };

    for (const child of els) {
      const box = child.getBoundingClientRect();
      const offset = y - (box.top + box.height / 2);
      if (offset < 0 && offset > closest.offset) {
        closest = { offset, element: child };
      }
    }
    return closest.element;
  }

  function ensurePlaceholder(container, beforeEl) {
    // Solo mover el placeholder si cambia realmente de posición (reduce jitter)
    if (!placeholder.parentNode || placeholder.parentNode !== container) {
      container.appendChild(placeholder);
    }
    if (beforeEl == null) {
      if (placeholder !== container.lastElementChild) {
        container.appendChild(placeholder);
      }
      return;
    }
    if (placeholder.nextSibling !== beforeEl) {
      container.insertBefore(placeholder, beforeEl);
    }
  }

  // --- Drag start/end ---
  document.querySelectorAll('.draggable').forEach(card => {
    card.addEventListener('dragstart', (e) => {
      draggedCard = card;
      card.classList.add('dragging');
      e.dataTransfer.setData('text/plain', card.getAttribute('data-task-id'));
      e.dataTransfer.effectAllowed = 'move';
    });

    card.addEventListener('dragend', () => {
      if (draggedCard) draggedCard.classList.remove('dragging');
      draggedCard = null;
      placeholder.remove();
      // limpiar clases de columnas
      document.querySelectorAll('.droppable.drag-over').forEach(c => c.classList.remove('drag-over'));
      enterCounter.clear();
    });
  });

  // --- Columnas droppable ---
  document.querySelectorAll('.droppable').forEach(col => {
    const container = col.querySelector('.tasks-container');
    enterCounter.set(col, 0);

    // Estos dos (enter/leave) con contador evitan el parpadeo
    col.addEventListener('dragenter', (e) => {
      e.preventDefault();
      enterCounter.set(col, (enterCounter.get(col) || 0) + 1);
      col.classList.add('drag-over');
    });

    col.addEventListener('dragleave', () => {
      const count = (enterCounter.get(col) || 0) - 1;
      enterCounter.set(col, count);

      if (count <= 0) {
        col.classList.remove('drag-over');
        placeholder.remove();
        enterCounter.set(col, 0);
      }
    });

    // dragover en la columna (no en el container) para que no “salte”
    col.addEventListener('dragover', (e) => {
      e.preventDefault();
      if (!draggedCard) return;

      // Determinar posición de inserción usando el container
      const after = getAfterElement(container, e.clientY);
      ensurePlaceholder(container, after);
    });

    col.addEventListener('drop', async (e) => {
      e.preventDefault();

      const taskId = e.dataTransfer.getData('text/plain');
      const card = document.getElementById('task-' + taskId);
      if (!card) return;

      const fromColumnId = card.getAttribute('data-current-column-id');
      const toColumnId = col.getAttribute('data-column-id');

      // Insertar la tarjeta en el lugar del placeholder
      if (placeholder.parentNode === container) {
        container.insertBefore(card, placeholder);
        // ✅ Actualizar visual ya mismo
        card.setAttribute('data-current-column-id', toColumnId);
        updateCardBar(card, col);

        placeholder.remove();
      } else {
        container.appendChild(card);

        card.setAttribute('data-current-column-id', toColumnId);
        const toColEl = document.querySelector(`.droppable[data-column-id="${toColumnId}"]`);
        updateCardBar(card, toColEl);

      }

      // Quitar “Sin tareas…” en destino
      const emptyDest = container.querySelector('.empty-col');
      if (emptyDest) emptyDest.remove();

      // Si origen quedó vacío, mostrar mensaje
      let fromOrder = '';
      if (String(fromColumnId) !== String(toColumnId)) {
        const fromContainer = document.querySelector(`.droppable[data-column-id="${fromColumnId}"] .tasks-container`);
        if (fromContainer) {
          if (fromContainer.querySelectorAll('.draggable').length === 0) {
            if (!fromContainer.querySelector('.empty-col')) {
              const p = document.createElement('p');
              p.className = 'empty-col';
              p.innerHTML = '<em>Sin tareas en esta columna.</em>';
              fromContainer.appendChild(p);
            }
          }
          fromOrder = getTaskIds(fromContainer).join(',');
        }
      }

      // Orden destino (ya quedó reflejado en DOM)
      const toOrder = getTaskIds(container).join(',');

      // Actualizar dataset local
      card.setAttribute('data-current-column-id', toColumnId);

      try {
        const resp = await fetch(`<?= BASE_URL ?>?controller=tasks&action=moveAjax`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: new URLSearchParams({
            project_id: projectId,
            task_id: taskId,
            to_column_id: toColumnId,
            column_id: toColumnId, // compat
            from_column_id: fromColumnId,
            to_order: toOrder,
            from_order: fromOrder
          })
        });

        const data = await resp.json();
        if (!data.ok) throw new Error(data.message || 'Error');

        // Sincroniza select interno si existe
        const select = card.querySelector('select[name="column_id"]');
        if (select) select.value = toColumnId;

        window.showToast?.('success', 'Movimiento guardado');

      } catch (err) {
        window.showToast?.('error', 'No se pudo guardar. Se recargará el tablero.');
        setTimeout(() => location.reload(), 600);
      } finally {
        col.classList.remove('drag-over');
        enterCounter.set(col, 0);
      }
    });
  });

  // --- Auto-scroll simple (opcional, ayuda mucho) ---
  document.addEventListener('dragover', (e) => {
    const margin = 70;
    const speed = 12;
    if (e.clientY < margin) window.scrollBy(0, -speed);
    if (e.clientY > window.innerHeight - margin) window.scrollBy(0, speed);
  });


  function barClassByColumnName(name) {
  const n = (name || '').toLowerCase();
  if (n.includes('progreso')) return 'bar-doing';
  if (n.includes('hecho') || n.includes('done')) return 'bar-done';
  return 'bar-todo'; // por hacer / default
}



 


});

// =========================
// FIN Drag & Drop estilo Trello
// =========================


//--- Titulos editables ---
document.addEventListener('DOMContentLoaded', function () {
  function makeEditable(el) {
    el.addEventListener('dblclick', () => {
      const card = el.closest('.kanban-card');
      const taskId = card?.dataset?.taskId;
      if (!taskId) return;

      const field = el.dataset.field; // title | responsible
      const currentText = (el.textContent || '').trim();
      const isResponsible = field === 'responsible';

      // Evitar doble edición
      if (el.dataset.editing === '1') return;
      el.dataset.editing = '1';

      // Crear input
      const input = document.createElement('input');
      input.type = 'text';
      input.value = isResponsible && currentText === 'Sin responsable' ? '' : currentText;
      input.style.width = '100%';
      input.style.boxSizing = 'border-box';
      input.style.padding = '6px';
      input.style.borderRadius = '6px';
      input.style.border = '1px solid #ccc';
      input.style.fontSize = '14px';

      // Guardar refs para revertir
      const oldHTML = el.innerHTML;

      // Render input en lugar del texto
      el.innerHTML = '';
      el.appendChild(input);
      input.focus();
      input.select();

      const getPayload = () => {
        // Tomamos el título y responsable actuales del card (si el usuario editó uno solo)
        const titleEl = card.querySelector('.task-title');
        const respEl  = card.querySelector('.task-responsible');

        const titleText = titleEl
          ? (titleEl.dataset.editing === '1' && titleEl.querySelector('input')
              ? titleEl.querySelector('input').value.trim()
              : titleEl.textContent.trim())
          : '';

        const respText = respEl
          ? (respEl.dataset.editing === '1' && respEl.querySelector('input')
              ? respEl.querySelector('input').value.trim()
              : (respEl.textContent.trim() === 'Sin responsable' ? '' : respEl.textContent.trim()))
          : '';

        return { title: titleText, responsible: respText };
      };

      const cancel = () => {
        el.innerHTML = oldHTML;
        el.dataset.editing = '0';
        attachAgain(el); // vuelve a enganchar dblclick
      };

      const save = async () => {
        const payload = getPayload();
        if (!payload.title) {
          window.showToast?.('error', 'El título no puede estar vacío');
          return;
        }

        try {
          const resp = await fetch(`<?= BASE_URL ?>?controller=tasks&action=updateInlineAjax`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
              task_id: taskId,
              title: payload.title,
              responsible: payload.responsible
            })
          });

          const data = await resp.json();
          if (!data.ok) throw new Error(data.message || 'Error');

          // Actualizar DOM “final”
          const titleEl = card.querySelector('.task-title');
          const respEl  = card.querySelector('.task-responsible');

          if (titleEl) {
            titleEl.dataset.editing = '0';
            titleEl.textContent = data.title;
            attachAgain(titleEl);
          }

          if (respEl) {
            respEl.dataset.editing = '0';
            respEl.textContent = data.responsible ? data.responsible : 'Sin responsable';
            attachAgain(respEl);
          }

          window.showToast?.('success', 'Tarea actualizada');

        } catch (err) {
          window.showToast?.('error', 'No se pudo guardar: ' + err.message);
          // Volver a estado anterior del elemento editado
          cancel();
        }
      };

      // Eventos: Enter guarda, Esc cancela, blur guarda
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') save();
        if (e.key === 'Escape') cancel();
      });

      input.addEventListener('blur', () => {
        // Guardar al salir (comportamiento tipo Trello)
        save();
      });
    });
  }

  function attachAgain(el) {
    // Evita duplicar listeners: recreamos el elemento (simple y seguro)
    const clone = el.cloneNode(true);
    el.parentNode.replaceChild(clone, el);
    makeEditable(clone);
  }

  document.querySelectorAll('.editable').forEach(makeEditable);
});


    // cambio automatico de barra superior de la card
    function barClassByColumnName(name) {
      const n = (name || '').toLowerCase();
      if (n.includes('progreso')) return 'bar-doing';
      if (n.includes('hecho') || n.includes('done')) return 'bar-done';
      return 'bar-todo'; // por hacer / default
    }

    function flashColorByColumnName(name){
      const n = (name || '').toLowerCase();
      if (n.includes('progreso')) return 'rgba(251,191,36,0.45)'; // amarillo
      if (n.includes('hecho') || n.includes('done')) return 'rgba(52,211,153,0.45)'; // verde
      return 'rgba(248,113,113,0.45)'; // rojo (por hacer)
    }

    function bgClassByColumnName(name){
      const n = (name || '').toLowerCase();
      if (n.includes('progreso')) return 'bg-doing';
      if (n.includes('hecho') || n.includes('done')) return 'bg-done';
      return 'bg-todo';
    }




    function updateCardBar(cardEl, colEl) {
      console.log('Llamada a updateCardbar: cardEl=>'+cardEl+' colEl=>'+colEl);
      if (!cardEl || !colEl) return;

      const colName = colEl.getAttribute('data-column-name') || '';

      /* ---- Barrita superior ---- */
      const barClass = barClassByColumnName(colName);
      cardEl.classList.remove('bar-todo', 'bar-doing', 'bar-done');
      cardEl.classList.add(barClass);

      /* ---- Fondo de tarjeta ---- */
      const bgClass = bgClassByColumnName(colName);
      cardEl.classList.remove('bg-todo', 'bg-doing', 'bg-done');
      cardEl.classList.add(bgClass);

      /* ---- Wipe de barrita ---- */
      cardEl.classList.remove('wipe');
      void cardEl.offsetWidth;
      cardEl.classList.add('wipe');
      setTimeout(() => cardEl.classList.remove('wipe'), 320);

      /* ---- Flash suave ---- */
      const flashColor = flashColorByColumnName(colName);
      cardEl.style.setProperty('--flash-color', flashColor);

      cardEl.classList.remove('flash');
      void cardEl.offsetWidth;
      cardEl.classList.add('flash');
      setTimeout(() => cardEl.classList.remove('flash'), 260);
    }


    /* Inicializar el fondo correcto al cargar el tablero */
    document.querySelectorAll('.kanban-card').forEach(card => {
      const colId = card.getAttribute('data-current-column-id');
      const colEl = document.querySelector(`.droppable[data-column-id="${colId}"]`);
      if (colEl) updateCardBar(card, colEl);
    });


     // fin cambio automatico de barra superior de la card







// Touch: press & hold para iniciar "drag" (apoya móviles)
document.querySelectorAll('.draggable').forEach(card => {
  let pressTimer = null;

  card.addEventListener('touchstart', (e) => {
    if (e.touches.length !== 1) return;
    pressTimer = setTimeout(() => {
      card.classList.add('dragging'); // feedback
      window.showToast?.('info', 'Arrastra la tarjeta y suéltala en otra columna');
    }, 250);
  }, { passive: true });

  card.addEventListener('touchend', () => {
    clearTimeout(pressTimer);
    card.classList.remove('dragging');
  });
});






</script>
