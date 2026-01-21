<div class="auth-card">
  <h2 class="auth-title">Iniciar sesión</h2>
  <p class="auth-subtitle">Accede para gestionar tus proyectos y tareas.</p>

  <?php if (!empty($error)): ?>
    <div style="padding:10px;border-radius:12px;background:#fee2e2;border:1px solid #fecaca;margin-bottom:12px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="on">
    <div class="form-group">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required class="input" placeholder="tu@correo.com">
    </div>

    <div class="form-group">
      <label for="password">Contraseña</label>
      <input id="password" name="password" type="password" required class="input" placeholder="••••••••">
    </div>

    <div class="auth-actions">
      <button type="submit" class="btn btn-primary">Entrar</button>
    </div>
  </form>
</div>
