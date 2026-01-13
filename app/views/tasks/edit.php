<?php
// app/views/tasks/edit.php
?>
<h2>Editar tarea</h2>

<p>
    <a href="<?= BASE_URL ?>?controller=projects&action=show&id=<?= (int)$projectId ?>" class="btn-secondary">
        ← Volver al tablero del proyecto
    </a>
</p>

<form method="post" action="<?= BASE_URL ?>?controller=tasks&action=update">
    <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
    <input type="hidden" name="project_id" value="<?= (int)$projectId ?>">

    <label for="title">Título *</label>
    <input type="text" id="title" name="title" required
           value="<?= htmlspecialchars($task['title']) ?>">

    <label for="responsible">Responsable (opcional)</label>
    <input type="text" id="responsible" name="responsible"
            value="<?= htmlspecialchars($task['responsible'] ?? '') ?>">


    <label for="description">Descripción (opcional)</label>
    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>

    <button type="submit">Guardar cambios</button>
</form>
