<h2>👥 Miembros del Proyecto</h2>



<div style="margin-bottom:10px;">
  <strong><?= htmlspecialchars($project['name'] ?? 'Proyecto') ?></strong>
  <span style="opacity:.7;">— Gestión de miembros</span>
</div>



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

<?php
  $canManage = in_array($myRole, ['owner','admin'], true);
?>

<?php if ($canManage): ?>
  <div style="padding:12px;border-radius:12px;border:1px solid rgba(0,0,0,.1);background:rgba(255,255,255,.9);margin-bottom:12px;">
    <h3 style="margin:0 0 8px 0;">➕ Agregar miembro</h3>

    <form method="POST" action="<?= BASE_URL ?>?controller=projects&action=addMember" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
      <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">

      <div>
        <label>Email</label><br>
        <input name="email" type="email" required placeholder="usuario@correo.com"
               style="padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);min-width:260px;">
      </div>

      <div>
        <label>Rol</label><br>
        <select name="role" style="padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
          <option value="member">member</option>
          <option value="viewer">viewer</option>
          <option value="admin">admin</option>
          <option value="owner">owner</option>
        </select>
      </div>

      <button class="btn-icon" style="width:auto;padding:8px 12px;">Agregar</button>
    </form>
  </div>
<?php endif; ?>

<table style="width:100%;border-collapse:collapse;">
  <thead>
    <tr style="text-align:left;border-bottom:1px solid rgba(0,0,0,.12);">
      <th style="padding:8px;">Usuario</th>
      <th style="padding:8px;">Email</th>
      <th style="padding:8px;">Rol (proyecto)</th>
      <th style="padding:8px;">Acciones</th>
    </tr>
  </thead>

  <tbody>
    <?php foreach ($members as $m): ?>
      <tr style="border-bottom:1px solid rgba(0,0,0,.06);">
        <td style="padding:8px;"><?= htmlspecialchars($m['name']) ?></td>
        <td style="padding:8px;opacity:.8;"><?= htmlspecialchars($m['email']) ?></td>
        <td style="padding:8px;">
          <?php if ($canManage): ?>
            <form method="POST" action="<?= BASE_URL ?>?controller=projects&action=updateMemberRole" style="display:inline-flex;gap:8px;align-items:center;">
              <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">
              <input type="hidden" name="user_id" value="<?= (int)$m['id'] ?>">

              <select name="role" style="padding:6px 8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
                <?php foreach (['owner','admin','member','viewer'] as $r): ?>
                  <option value="<?= $r ?>" <?= ($m['role'] === $r) ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>

              <button class="btn-icon tip" data-tip="Guardar rol" style="width:30px;height:28px;">💾</button>
            </form>
          <?php else: ?>
            <span><?= htmlspecialchars($m['role']) ?></span>
          <?php endif; ?>
        </td>

        <td style="padding:8px;">
          <?php if ($canManage): ?>
            <form method="POST" action="<?= BASE_URL ?>?controller=projects&action=removeMember" style="display:inline;">
              <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">
              <input type="hidden" name="user_id" value="<?= (int)$m['id'] ?>">
              <button class="btn-action del tip"
                      data-tip="Eliminar miembro"
                      onclick="return confirm('¿Eliminar este miembro del proyecto?');">🗑️</button>
            </form>
          <?php else: ?>
            <span style="opacity:.6;">—</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>




<div style="margin-top:12px;">
  <a class="btn-action view tip"
     data-tip="Volver al tablero"
     href="<?= BASE_URL ?>?controller=projects&action=show&id=<?= (int)$project['id'] ?>">👁️</a>
</div>