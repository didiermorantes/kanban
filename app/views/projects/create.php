<?php
// app/views/projects/create.php
?>
<h2>Crear nuevo proyecto</h2>

<p>
    <a href="<?= BASE_URL ?>?controller=projects&action=index" class="btn-secondary">
        ← Volver al listado de proyectos
    </a>
</p>

<form method="post" action="<?= BASE_URL ?>?controller=projects&action=store">
    <label for="name">Nombre del proyecto *</label>
    <input type="text" id="name" name="name" required>

    <label for="responsible">Responsable</label>
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
    <textarea id="description" name="description" rows="4"></textarea>

    <button type="submit">Guardar proyecto</button>
</form>
