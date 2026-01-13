<h2>Iniciar sesión</h2>
<?php if (!empty($error)): ?>
  <div style="padding:10px;border-radius:10px;background:#fee2e2;border:1px solid #fecaca;">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<form method="POST" style="max-width:360px;margin-top:12px;">
  <div style="margin-bottom:10px;">
    <label>Email</label>
    <input name="email" type="email" required style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>
  <div style="margin-bottom:10px;">
    <label>Contraseña</label>
    <input name="password" type="password" required style="width:100%;padding:8px;border-radius:10px;border:1px solid rgba(0,0,0,.2);">
  </div>
  <button type="submit" class="btn-icon" style="width:auto;padding:8px 12px;">Entrar</button>
</form>
