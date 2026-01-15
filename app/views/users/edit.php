<h2>✏️ Editar usuario</h2>

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

<form method="POST" action="<?= BASE_URL ?>?controller=users&action=update" style="max-width:420px;">
  <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

  <div style="margin-bottom:10px;">
    <label>Nombre</label><br>
    <input name="name" required
           value="<?= htmlspecialchars($user['name'] ?? '') ?>"
           style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>

  <div style="margin-bottom:10px;">
    <label>Email</label><br>
    <input name="email" type="email" required
           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
           style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>

  <div style="margin-bottom:10px;">
    <label>Rol global</label><br>
    <select name="role" style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
      <?php foreach (['member','viewer','admin','owner'] as $r): ?>
        <option value="<?= $r ?>" <?= (($user['role'] ?? '') === $r) ? 'selected' : '' ?>><?= $r ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div style="margin-bottom:10px;">
    <label>Nueva contraseña (opcional)</label><br>
    <input name="password" type="password"
           placeholder="Dejar vacío para no cambiar"
           style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>

  <div style="display:flex;gap:10px;align-items:center;">
    <button class="btn-icon" style="width:auto;padding:8px 12px;">Guardar cambios</button>

    <a class="btn-action view tip" data-tip="Volver"
       href="<?= BASE_URL ?>?controller=users&action=index">↩️</a>
  </div>
</form>
