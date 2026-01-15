<h2>➕ Crear usuario</h2>

<?php if (!empty($error)): ?>
  <div style="padding:10px;border-radius:10px;background:#fee2e2;border:1px solid #fecaca;margin-bottom:10px;">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>?controller=users&action=store" style="max-width:420px;">
  <div style="margin-bottom:10px;">
    <label>Nombre</label><br>
    <input name="name" required
           style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>

  <div style="margin-bottom:10px;">
    <label>Email</label><br>
    <input name="email" type="email" required
           style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>

  <div style="margin-bottom:10px;">
    <label>Contraseña</label><br>
    <input name="password" type="password" required
           style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>

  <div style="margin-bottom:10px;">
    <label>Rol global</label><br>
    <select name="role" style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
      <option value="member">member</option>
      <option value="viewer">viewer</option>
      <option value="admin">admin</option>
      <option value="owner">owner</option>
    </select>
  </div>

  <div style="display:flex;gap:10px;">
    <button class="btn-icon" style="width:auto;padding:8px 12px;">Guardar</button>
    <a class="btn-action view tip" data-tip="Volver"
       href="<?= BASE_URL ?>?controller=users&action=index">↩️</a>
  </div>
</form>
