<h2>👤 Usuarios</h2>

<?php if (!empty($error)): ?>
  <div style="padding:10px;border-radius:10px;background:#fee2e2;border:1px solid #fecaca;margin-bottom:10px;">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<?php if (!empty($ok)): ?>
  <div style="padding:10px;border-radius:10px;background:#dcfce7;border:1px solid #bbf7d0;margin-bottom:10px;">
    <?= htmlspecialchars($ok) ?>
  </div>
<?php endif; ?>

<div style="margin: 10px 0;">
  <a class="btn-icon" style="width:auto;padding:8px 12px;"
     href="<?= BASE_URL ?>?controller=users&action=create">➕ Nuevo usuario</a>
</div>

<table style="width:100%;border-collapse:collapse;">
  <thead>
    <tr style="text-align:left;border-bottom:1px solid rgba(0,0,0,.12);">
      <th style="padding:8px;">Nombre</th>
      <th style="padding:8px;">Email</th>
      <th style="padding:8px;">Rol global</th>
      <th style="padding:8px;">Creado</th>
      <th style="padding:8px;">Acciones</th>

    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr style="border-bottom:1px solid rgba(0,0,0,.06);">
        <td style="padding:8px;"><?= htmlspecialchars($u['name']) ?></td>
        <td style="padding:8px;opacity:.85;"><?= htmlspecialchars($u['email']) ?></td>
        <td style="padding:8px;"><strong><?= htmlspecialchars($u['role']) ?></strong></td>
        <td style="padding:8px;opacity:.75;"><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
        <td style="padding:8px;white-space:nowrap;">
            <a class="btn-action edit tip" data-tip="Editar"
                href="<?= BASE_URL ?>?controller=users&action=edit&id=<?= (int)$u['id'] ?>">✏️</a>

            <form method="POST" action="<?= BASE_URL ?>?controller=users&action=destroy" style="display:inline;">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn-action del tip" data-tip="Eliminar"
                        onclick="return confirm('¿Eliminar este usuario?');">🗑️</button>
            </form>
        </td>

      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
