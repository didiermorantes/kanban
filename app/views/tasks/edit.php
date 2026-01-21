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

    <label for="responsible_user_id">Responsable (opcional)</label>
    <select id="responsible_user_id" name="responsible_user_id">
        <option value="">Sin responsable</option>
        <?php foreach (($members ?? []) as $m): ?>
            <option value="<?= (int)$m['id'] ?>" <?= ((int)($task['responsible_user_id'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['role']) ?>)
            </option>
        <?php endforeach; ?>
    </select>


    <label for="description">Descripción (opcional)</label>
    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>

    <button type="submit">Guardar cambios</button>
</form>
