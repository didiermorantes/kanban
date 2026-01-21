<?php
// app/views/projects/edit.php
?>
<h2>Editar proyecto</h2>

<div style="margin-top:12px;" class="project-actions">
  <a class="btn-action view tip"
     data-tip="Volver al tablero"
     href="<?= BASE_URL ?>?controller=projects&action=show&id=<?= (int)$project['id'] ?>">👁️</a>
</div>

<form method="post" action="<?= BASE_URL ?>?controller=projects&action=update">
    <input type="hidden" name="id" value="<?= (int)$project['id'] ?>">

    <label for="name">Nombre del proyecto *</label>
    <input type="text" id="name" name="name" required
           value="<?= htmlspecialchars($project['name']) ?>">

           <label for="responsible_user_id">Responsable del proyecto</label>
            <select id="responsible_user_id" name="responsible_user_id">
            <option value="0">Sin responsable</option>
            <?php foreach ($members as $m): ?>
                <option value="<?= (int)$m['id'] ?>"
                <?= ((int)($project['responsible_user_id'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['email']) ?>)
                </option>
            <?php endforeach; ?>
            </select>



    <label for="description">Descripción (opcional)</label>
    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>

    <button type="submit">Guardar cambios</button>
</form>
