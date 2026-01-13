<?php
// app/views/projects/index.php
?>
<h2>Listado de proyectos</h2>

<div class="top-actions">
    <a href="<?= BASE_URL ?>?controller=projects&action=create">
        <button>+ Nuevo proyecto</button>
    </a>
</div>


<table>
    <thead>
        <tr>
            <th>Proyecto</th>
            <th>Responsables</th>
            <th>Descripción</th>
            <th>Avance</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($projects)): ?>
        <tr>
            <td colspan="4">No hay proyectos registrados.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <tr>
                <td>
                        <?= htmlspecialchars($project['name']) ?> <br />
                        <?php
                        $pid = (int)$project['id'];
                        $counts = $taskCounts[$pid] ?? ['todo'=>0, 'doing'=>0, 'done'=>0];
                        ?>
                        <div class="chips">
                        <span class="chip todo tip"  data-tip="Por hacer: <?= (int)$counts['todo'] ?>">
                            <i class="dot"></i> <?= (int)$counts['todo'] ?>
                        </span>

                        <span class="chip doing tip"  data-tip="En progreso: <?= (int)$counts['doing'] ?>">
                            <i class="dot"></i> <?= (int)$counts['doing'] ?>
                        </span>

                        <span class="chip done tip"  data-tip="Hecho: <?= (int)$counts['done'] ?>">
                            <i class="dot"></i> <?= (int)$counts['done'] ?>
                        </span>
                        </div>
           
                </td>
                <!-- RESPONSABLES -->
                <td>
                    <?php $pr = trim($project['project_responsible_name'] ?? ''); ?>
                    <span class="badge-resp <?= $pr === '' ? 'muted' : '' ?>">
                        <span class="avatar"><?= htmlspecialchars($pr !== '' ? mb_strtoupper(mb_substr($pr,0,1)) : '—') ?></span>
                        <span class="name"><?= htmlspecialchars($pr !== '' ? $pr : 'Sin responsable') ?></span>
                    </span>
                </td>

                <td><?= htmlspecialchars($project['description'] ?? '') ?></td>
                <td>
                                    <?php
                                    $progress = (int)($project['progress_percentage'] ?? 0);
                                    if ($progress < 0) $progress = 0;
                                    if ($progress > 100) $progress = 100;

                                    $progressClass = 'progress-red';
                                    if ($progress <= 49) $progressClass = 'progress-red';
                                    else if ($progress <= 74) $progressClass = 'progress-yellow';
                                    else $progressClass = 'progress-green';
                                    ?>

                                    <div style="display:flex; align-items:center;">
                                    <div class="progress-wrap" title="Avance: <?= $progress ?>%">
                                        <div class="progress-bar <?= $progressClass ?>" style="width: <?= $progress ?>%;"></div>
                                    </div>
                                    <span class="progress-text"><?= $progress ?>%</span>
                                    </div>

                </td>
                <td  style="white-space: nowrap;">
                    <a href="<?= BASE_URL ?>?controller=projects&action=show&id=<?= (int)$project['id'] ?>"
                     class="btn-action view tip"
                     data-tip="Ver tablero">
                    👁️
                    </a>

                    <a href="<?= BASE_URL ?>?controller=projects&action=edit&id=<?= (int)$project['id'] ?>"
                    class="btn-action edit tip"
                    data-tip="Editar Proyecto">
                    ✏️
                    </a>
                    <form method="post"
                        action="<?= BASE_URL ?>?controller=projects&action=destroy"
                        style="display:inline"
                        onsubmit="return confirm('¿Seguro que deseas eliminar este proyecto? Se borrarán también sus tareas.');">
                        <input type="hidden" name="id" value="<?= (int)$project['id'] ?>">
                        <button class="btn-action del tip" type="submit" class="btn-secondary"  data-tip="Eliminar Proyecto">🗑️</button>
                    </form>
                </td>

            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>


<h2>📁 Proyectos</h2>

<div class="projects-grid">
  <?php foreach ($projects as $project): ?>
    <?php
      $pid = (int)$project['id'];

      // progreso
      $progress = (int)($project['progress'] ?? 0);
      if ($progress < 0) $progress = 0;
      if ($progress > 100) $progress = 100;

      $progressClass = 'progress-red';
      if ($progress <= 35) $progressClass = 'progress-red';
      else if ($progress <= 85) $progressClass = 'progress-yellow';
      else $progressClass = 'progress-green';

      // conteos
      $counts = $taskCounts[$pid] ?? ['todo'=>0, 'doing'=>0, 'done'=>0];

      // responsable del proyecto (si lo estás mostrando)
      $responsableProyecto = trim($project['responsible'] ?? '');
    ?>

    <div class="project-card">
      <div class="pc-head">
        <div>
          <h3 class="pc-title"><?= htmlspecialchars($project['name']) ?></h3>
          <div class="pc-sub">
            <?= $responsableProyecto !== '' ? ('👤 ' . htmlspecialchars($responsableProyecto)) : '👤 Sin responsable' ?>
          </div>
        </div>

        <div class="pc-actions">
          <a href="<?= BASE_URL ?>?controller=projects&action=show&id=<?= $pid ?>"
             class="btn-action view tip"
             data-tip="Ver tablero">👁️</a>

          <a href="<?= BASE_URL ?>?controller=projects&action=edit&id=<?= $pid ?>"
             class="btn-action edit tip"
             data-tip="Editar proyecto">✏️</a>

          <a href="<?= BASE_URL ?>?controller=projects&action=delete&id=<?= $pid ?>"
             class="btn-action del tip"
             data-tip="Eliminar proyecto"
             onclick="return confirm('¿Eliminar este proyecto?');">🗑️</a>
        </div>
      </div>

      <div class="pc-section">
        <div class="pc-row">
          <div style="display:flex; align-items:center;">
            <div class="progress-wrap tip" data-tip="Avance: <?= $progress ?>%">
              <div class="progress-bar <?= $progressClass ?>" style="width: <?= $progress ?>%;"></div>
            </div>
            <span class="progress-text"><?= $progress ?>%</span>
          </div>
        </div>
      </div>

      <div class="pc-section">
        <div class="chips">
          <span class="chip todo tip" data-tip="Por hacer: <?= (int)$counts['todo'] ?>">
            <i class="dot"></i> <?= (int)$counts['todo'] ?>
          </span>

          <span class="chip doing tip" data-tip="En progreso: <?= (int)$counts['doing'] ?>">
            <i class="dot"></i> <?= (int)$counts['doing'] ?>
          </span>

          <span class="chip done tip" data-tip="Hecho: <?= (int)$counts['done'] ?>">
            <i class="dot"></i> <?= (int)$counts['done'] ?>
          </span>
        </div>
      </div>

    </div>
  <?php endforeach; ?>
</div>

