<?php
// app/views/projects/report.php
$colSeries = $report['colSeries'];
$throughput = $report['throughputSeries'];
$avgCycle = $report['avgCycleHours'];
$taskTimes = $report['taskTimes'];
?>

<h2>📊 Métricas Kanban – <?= htmlspecialchars($project['name']) ?></h2>

<p>
  <a href="<?= BASE_URL ?>?controller=projects&action=show&id=<?= (int)$project['id'] ?>" class="btn-secondary">
    ← Volver al tablero
  </a>
</p>

<div style="display:flex; gap:16px; flex-wrap:wrap;">
  <div style="background:#fff; padding:12px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08); min-width:260px;">
    <strong>Cycle time promedio (creación → hecho)</strong><br>
    <span style="font-size:22px;"><?= htmlspecialchars($avgCycle) ?> h</span>
  </div>
</div>
<br>

<?php $avgCycleReal = $report['avgCycleRealHours'] ?? 0; ?>
<div style="background:#fff; padding:12px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08); min-width:260px;">
  <strong>Cycle time real (En progreso → Hecho)</strong><br>
  <span style="font-size:22px;"><?= htmlspecialchars($avgCycleReal) ?> h</span>
</div>



<h3>1) Tiempo total por columna (horas)</h3>
<canvas id="colChart" width="900" height="260" style="background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08);"></canvas>


<h3>2) Throughput (tareas completadas por día)</h3>
<canvas id="tpChart" width="900" height="260" style="background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08);"></canvas>

<h3>3) Detalle por tarea (horas por columna)</h3>



<table>
  <thead>
    <tr>
      <th>Tarea</th>
      <th>Responsable</th>
      <th>Creada</th>
      <th>Completada</th>
      <th>Tiempo por columna (h)</th>
    </tr>
  </thead>
  <tbody>


    <?php
    $colNameById = [];
    foreach (($report['columns'] ?? []) as $c) $colNameById[(int)$c['id']] = $c['name'];

    function colColorByName(string $name): string {
    $lower = mb_strtolower($name);
    if (strpos($lower, 'hacer') !== false) return '#f87171'; // rojo
    if (strpos($lower, 'progreso') !== false) return '#fbbf24'; // amarillo
    if (strpos($lower, 'hecho') !== false || strpos($lower, 'done') !== false) return '#34d399'; // verde
    return '#93c5fd'; // fallback
    }
    ?>

    <div class="taskbar-legend">
    <span><i class="taskbar-dot" style="background:#f87171"></i>Por hacer</span>
    <span><i class="taskbar-dot" style="background:#fbbf24"></i>En progreso</span>
    <span><i class="taskbar-dot" style="background:#34d399"></i>Hecho</span>
    </div>




    <?php foreach ($taskTimes as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['title']) ?></td>
        <td><?= htmlspecialchars($t['responsible'] ?: '—') ?></td>
        <td><?= htmlspecialchars($t['created_at']) ?></td>
        <td><?= htmlspecialchars($t['completed_at'] ?: '—') ?></td>
        <td>

        <?php foreach ($t['times_by_col_hours'] as $cid => $hours): ?>
            <?php
                $colName = $colNameById[$cid] ?? ('Columna #' . (int)$cid);
                $lower = mb_strtolower($colName);
                $badgeStyle = 'background:#e5e7eb; color:#111827;';

                if (strpos($lower, 'hacer') !== false) $badgeStyle = 'background:#f87171; color:#111827;';
                if (strpos($lower, 'progreso') !== false) $badgeStyle = 'background:#fbbf24; color:#111827;';
                if (strpos($lower, 'hecho') !== false || strpos($lower, 'done') !== false) $badgeStyle = 'background:#34d399; color:#111827;';
            ?>
            <div style="display:flex; align-items:center; gap:8px; margin:4px 0;">
                <span style="padding:2px 8px; border-radius:999px; font-size:12px; <?= $badgeStyle ?>">
                <?= htmlspecialchars($colName) ?>
                </span>
                <span><?= round($hours, 2) ?>h</span>
            </div>






        <?php endforeach; ?>

            <?php
                $times = $t['times_by_col_hours'] ?? [];
                // Quitar ruidos de 0h
                $times = array_filter($times, fn($h) => $h >= 0.01);

                $totalH = array_sum($times);
                ?>

                <?php if (empty($times) || $totalH <= 0): ?>
                <em>Sin datos</em>
                <?php else: ?>

                   
                    <?php
                     // detectar horas en progreso y detectar si está stuck
                    $times = $t['times_by_col_hours'] ?? [];
                    $times = array_filter($times, fn($h) => $h >= 0.01);
                    $totalH = array_sum($times);

                    $inProgressHours = 0.0;
                    foreach ($times as $cid => $hours) {
                    $colName = $colNameById[(int)$cid] ?? '';
                    if (mb_stripos($colName, 'progreso') !== false) {
                        $inProgressHours += (float)$hours;
                    }
                    }
                    $isStuck = $inProgressHours >= 72;
                    ?>


                <div class="taskbar <?= $isStuck ? 'alert-stuck' : '' ?>"
                    title="<?= $isStuck ? '⚠️ Esta tarea lleva ' . round($inProgressHours,2) . 'h en progreso' : 'Distribución del tiempo por columna' ?>">

                    <?php foreach ($times as $cid => $hours): ?>
                    <?php
                        $colName = $colNameById[(int)$cid] ?? ('Columna #' . (int)$cid);
                        $color = colColorByName($colName);

                        // porcentaje mínimo visible (para que segmentos muy pequeños se vean)
                        $pct = ($hours / $totalH) * 100;
                        $pct = max($pct, 2); // mínimo 2%

                        $tip = $colName . ': ' . round($hours, 2) . 'h';
                    ?>
                    <div class="taskbar-seg"
                        style="width: <?= $pct ?>%; background: <?= $color ?>;"
                        data-tip="<?= htmlspecialchars($tip) ?>"></div>
                    <?php endforeach; ?>
                </div>

                <div style="font-size:12px; margin-top:6px; opacity:0.85;">
                    Total: <strong><?= round($totalH, 2) ?>h</strong>
                </div>
            <?php endif; ?>


        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


<h3>4) WIP -Work In Progress- actual (trabajo en curso)</h3>
<table>
  <thead>
    <tr><th>Columna</th><th>Cantidad</th></tr>
  </thead>
  <tbody>
    <?php foreach (($report['wipSeries'] ?? []) as $w): ?>
      <tr>
        <td><?= htmlspecialchars($w['name']) ?></td>
        <td><?= (int)$w['count'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


<h3>5) Aging WIP (Top 10 tareas más “viejas” en su columna actual)</h3>
<table>
  <thead>
    <tr>
      <th>Tarea</th>
      <th>Responsable</th>
      <th>Columna actual</th>
      <th>Desde</th>
      <th>Edad (días)</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $colNameById = [];
      foreach (($report['columns'] ?? []) as $c) $colNameById[(int)$c['id']] = $c['name'];
    ?>
    <?php foreach (($report['agingTop'] ?? []) as $a): ?>
      <tr>
        <td><?= htmlspecialchars($a['title']) ?></td>
        <td><?= htmlspecialchars($a['responsible'] ?: '—') ?></td>
        <td><?= htmlspecialchars($colNameById[$a['column_id']] ?? ('#'.$a['column_id'])) ?></td>
        <td><?= htmlspecialchars($a['entered_at']) ?></td>
        <td><?= htmlspecialchars($a['age_days']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>6) CFD – Cumulative Flow Diagram (conteo por columna por día)</h3>
<canvas id="cfdChart" width="900" height="320"
        style="background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08);"></canvas>


<script>
const colSeries = <?= json_encode($colSeries) ?>;
const throughput = <?= json_encode($throughput) ?>;

/*
Grafico de barra sin colores
function drawBarChart(canvasId, labels, values) {
  const c = document.getElementById(canvasId);
  const ctx = c.getContext('2d');
  ctx.clearRect(0,0,c.width,c.height);

  const pad = 40;
  const w = c.width - pad*2;
  const h = c.height - pad*2;

  const maxV = Math.max(1, ...values);
  const barW = w / Math.max(1, values.length);

  // axis
  ctx.beginPath();
  ctx.moveTo(pad, pad);
  ctx.lineTo(pad, pad + h);
  ctx.lineTo(pad + w, pad + h);
  ctx.stroke();

  values.forEach((v, i) => {
    const bh = (v / maxV) * (h - 10);
    const x = pad + i*barW + 8;
    const y = pad + h - bh;

    ctx.fillRect(x, y, Math.max(8, barW-16), bh);

    // labels
    ctx.save();
    ctx.translate(x, pad + h + 14);
    ctx.rotate(-0.3);
    ctx.fillText(labels[i].slice(0, 14), 0, 0);
    ctx.restore();

    // value
    ctx.fillText(v.toFixed(1), x, y - 6);
  });
}
*/

function drawBarChart(canvasId, labels, values, colors) {
  const c = document.getElementById(canvasId);
  const ctx = c.getContext('2d');
  ctx.clearRect(0, 0, c.width, c.height);

  const pad = 40;
  const w = c.width - pad * 2;
  const h = c.height - pad * 2;

  const maxV = Math.max(1, ...values);
  const barW = w / Math.max(1, values.length);

  // Ejes
  ctx.beginPath();
  ctx.moveTo(pad, pad);
  ctx.lineTo(pad, pad + h);
  ctx.lineTo(pad + w, pad + h);
  ctx.strokeStyle = '#111827';
  ctx.stroke();

  values.forEach((v, i) => {
    const bh = (v / maxV) * (h - 10);
    const x = pad + i * barW + 8;
    const y = pad + h - bh;

    // Color por columna
    ctx.fillStyle = colors?.[i] || '#93c5fd';
    ctx.fillRect(x, y, Math.max(12, barW - 16), bh);

    // Valor arriba
    ctx.fillStyle = '#111827';
    ctx.font = '12px Arial';
    ctx.fillText(v.toFixed(1) + 'h', x, y - 6);

    // Etiqueta eje X
    ctx.save();
    ctx.translate(x, pad + h + 14);
    ctx.rotate(-0.35);
    ctx.fillText(labels[i], 0, 0);
    ctx.restore();
  });
}



function drawLineChart(canvasId, labels, values) {
  const c = document.getElementById(canvasId);
  const ctx = c.getContext('2d');
  ctx.clearRect(0,0,c.width,c.height);

  const pad = 40;
  const w = c.width - pad*2;
  const h = c.height - pad*2;

  const maxV = Math.max(1, ...values);

  // axis
  ctx.beginPath();
  ctx.moveTo(pad, pad);
  ctx.lineTo(pad, pad + h);
  ctx.lineTo(pad + w, pad + h);
  ctx.stroke();

  if (values.length === 0) return;

  ctx.beginPath();
  values.forEach((v, i) => {
    const x = pad + (i/(values.length-1 || 1)) * w;
    const y = pad + h - (v/maxV)*(h-10);
    if (i === 0) ctx.moveTo(x,y);
    else ctx.lineTo(x,y);
  });
  ctx.stroke();

  values.forEach((v, i) => {
    const x = pad + (i/(values.length-1 || 1)) * w;
    const y = pad + h - (v/maxV)*(h-10);
    ctx.beginPath();
    ctx.arc(x,y,3,0,Math.PI*2);
    ctx.fill();

    // labels (cada 2 para no saturar)
    if (i % 2 === 0) ctx.fillText(labels[i], x-18, pad + h + 16);
  });
}

/*
Tiempo total por columna (horas) sin colores
drawBarChart(
  'colChart',
  colSeries.map(x => x.name),
  colSeries.map(x => x.total_hours)
);
*/

drawBarChart(
  'colChart',
  colSeries.map(x => x.name),
  colSeries.map(x => x.total_hours),
  colSeries.map(x => {
    const name = x.name.toLowerCase();
    if (name.includes('hacer')) return '#f87171';        // Por hacer
    if (name.includes('progreso')) return '#fbbf24';     // En progreso
    if (name.includes('hecho') || name.includes('done')) return '#34d399'; // Hecho
    return '#93c5fd'; // fallback (azul suave)
  })
);


drawLineChart(
  'tpChart',
  throughput.map(x => x.day),
  throughput.map(x => x.count)
);


/*
Grafico  CFD – Cumulative Flow Diagram (conteo por columna por día) tipo linea

const cfdSeries = <?= json_encode($report['cfdSeries'] ?? []) ?>;

function drawMultiLineChart(canvasId, series) {
  const c = document.getElementById(canvasId);
  const ctx = c.getContext('2d');
  ctx.clearRect(0,0,c.width,c.height);

  const pad = 40;
  const w = c.width - pad*2;
  const h = c.height - pad*2;

  // axis
  ctx.beginPath();
  ctx.moveTo(pad, pad);
  ctx.lineTo(pad, pad + h);
  ctx.lineTo(pad + w, pad + h);
  ctx.stroke();

  if (!series.length) return;

  // labels (tomamos días desde la primera serie)
  const labels = series[0].points.map(p => p.day);
  const maxV = Math.max(1, ...series.flatMap(s => s.points.map(p => p.count)));

  // helper to map
  const xAt = (i) => pad + (i / Math.max(1, labels.length-1)) * w;
  const yAt = (v) => pad + h - (v / maxV) * (h - 10);

  // Dibujar una línea por columna (sin colores fijos; el canvas usará el color actual,
  // y se diferencian por desplazamiento sutil con labels)
  series.forEach((s, idx) => {
    ctx.beginPath();
    s.points.forEach((p, i) => {
      const x = xAt(i);
      const y = yAt(p.count);
      if (i === 0) ctx.moveTo(x,y);
      else ctx.lineTo(x,y);
    });
    ctx.stroke();

    // Leyenda simple (texto)
    ctx.fillText(s.name.slice(0, 18), pad + 10, pad + 14 + idx*14);
  });

  // Eje X: pocas etiquetas para no saturar
  for (let i = 0; i < labels.length; i += 5) {
    ctx.fillText(labels[i], xAt(i) - 18, pad + h + 16);
  }
  // Eje Y: max
  ctx.fillText(String(maxV), 10, pad + 10);
}

drawMultiLineChart('cfdChart', cfdSeries);

*/

// Grafico  CFD – Cumulative Flow Diagram  (conteo por columna por día) tipo Area
const cfdSeries = <?= json_encode($report['cfdSeries'] ?? []) ?>;

// Paleta simple (puedes cambiarla luego)
const CFD_COLORS = [
  '#f87171', // rojo
  '#fbbf24', // amarillo
  '#34d399', // verde
  '#60a5fa', // azul
  '#a78bfa', // morado
  '#fb7185', // rosa
  '#22c55e', // verde fuerte
  '#38bdf8', // celeste
];

function ensureTooltip() {
  let tip = document.getElementById('cfdTooltip');
  if (tip) return tip;

  tip = document.createElement('div');
  tip.id = 'cfdTooltip';
  tip.style.position = 'fixed';
  tip.style.zIndex = '3000';
  tip.style.pointerEvents = 'none';
  tip.style.background = 'rgba(0,0,0,0.78)';
  tip.style.color = '#fff';
  tip.style.padding = '8px 10px';
  tip.style.borderRadius = '8px';
  tip.style.fontSize = '12px';
  tip.style.lineHeight = '1.3';
  tip.style.display = 'none';
  document.body.appendChild(tip);
  return tip;
}

function drawStackedAreaCFD(canvasId, series) {
  const c = document.getElementById(canvasId);
  const ctx = c.getContext('2d');
  ctx.clearRect(0,0,c.width,c.height);

  const pad = 48;
  const w = c.width - pad*2;
  const h = c.height - pad*2;

  // Ejes
  ctx.beginPath();
  ctx.moveTo(pad, pad);
  ctx.lineTo(pad, pad + h);
  ctx.lineTo(pad + w, pad + h);
  ctx.strokeStyle = '#111827';
  ctx.lineWidth = 1;
  ctx.stroke();

  if (!series || series.length === 0) return;

  // Labels (días) desde la primera serie
  const labels = series[0].points.map(p => p.day);
  const n = labels.length;
  if (n === 0) return;

  // Matriz values[colIndex][i]
  const values = series.map(s => s.points.map(p => Number(p.count || 0)));

  // Calcular total por día y max
  const totals = Array.from({length:n}, (_, i) => values.reduce((acc, col) => acc + (col[i] || 0), 0));
  const maxV = Math.max(1, ...totals);

  const xAt = (i) => pad + (i / Math.max(1, n-1)) * w;
  const yAt = (v) => pad + h - (v / maxV) * (h - 10);

  // Grid suave Y
  ctx.strokeStyle = 'rgba(0,0,0,0.08)';
  ctx.lineWidth = 1;
  const ticks = 4;
  for (let t=1; t<=ticks; t++) {
    const y = pad + (h/ticks)*t;
    ctx.beginPath();
    ctx.moveTo(pad, y);
    ctx.lineTo(pad+w, y);
    ctx.stroke();
  }

  // Dibujar áreas apiladas
  // base[i] = acumulado inferior; top[i] = acumulado superior
  let base = Array(n).fill(0);

  // Guardamos “polígonos” para tooltip (un rango vertical por columna en x cercano)
  const hitData = []; // [{name,color, lower[], upper[] }]

  series.forEach((s, colIdx) => {
    const col = values[colIdx];
    const upper = col.map((v, i) => base[i] + v);

    // Área (polígono): (x, upper) ida + (x, base) vuelta
    ctx.beginPath();
    for (let i=0; i<n; i++) ctx.lineTo(xAt(i), yAt(upper[i]));
    for (let i=n-1; i>=0; i--) ctx.lineTo(xAt(i), yAt(base[i]));
    ctx.closePath();

    const color = CFD_COLORS[colIdx % CFD_COLORS.length];
    ctx.fillStyle = color + 'B3'; // + alpha (~70%)
    ctx.strokeStyle = color;
    ctx.lineWidth = 1;
    ctx.fill();
    ctx.stroke();

    hitData.push({
      name: s.name,
      color,
      lower: [...base],
      upper: [...upper],
    });

    base = upper; // siguiente capa parte desde aquí
  });

  // ======================
    // Línea total WIP (BONUS)
    // ======================
    ctx.save();
    ctx.beginPath();
    for (let i = 0; i < n; i++) {
    const x = xAt(i);
    const y = yAt(totals[i]);
    if (i === 0) ctx.moveTo(x, y);
    else ctx.lineTo(x, y);
    }
    ctx.strokeStyle = '#111827'; // negro/gris oscuro
    ctx.lineWidth = 2;
    ctx.stroke();

    // puntos sutiles para lectura
    for (let i = 0; i < n; i += 5) {
    const x = xAt(i);
    const y = yAt(totals[i]);
    ctx.beginPath();
    ctx.arc(x, y, 2.5, 0, Math.PI * 2);
    ctx.fillStyle = '#111827';
    ctx.fill();
    }
    ctx.restore();



  // Etiquetas eje X (cada 5)
  ctx.fillStyle = '#111827';
  ctx.font = '12px Arial';
  for (let i=0; i<n; i+=5) {
    ctx.fillText(labels[i], xAt(i) - 22, pad + h + 18);
  }

  // Etiquetas eje Y
  ctx.fillText(String(maxV), 10, pad + 10);
  ctx.fillText("0", 18, pad + h);

  // Leyenda
  const legendX = pad + w + 12;
  const legendY = pad;
  // si no cabe, ponemos leyenda arriba dentro
  const insideLegend = (legendX + 140 > c.width);
  let lx = insideLegend ? pad + 10 : legendX;
  let ly = insideLegend ? pad + 10 : legendY;


  // Leyenda línea total
    ctx.strokeStyle = '#111827';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(lx, ly + series.length * 16 + 8);
    ctx.lineTo(lx + 12, ly + series.length * 16 + 8);
    ctx.stroke();
    ctx.fillStyle = '#111827';
    ctx.fillText('Total WIP', lx + 16, ly + series.length * 16 + 12);



  ctx.save();
  ctx.globalAlpha = 1;
  series.forEach((s, idx) => {
    const color = CFD_COLORS[idx % CFD_COLORS.length];
    ctx.fillStyle = color;
    ctx.fillRect(lx, ly + idx*16, 10, 10);
    ctx.fillStyle = '#111827';
    ctx.fillText(s.name.slice(0, 20), lx + 14, ly + idx*16 + 10);
  });
  ctx.restore();

  // Tooltip por hover
  const tip = ensureTooltip();
  function onMove(ev) {
    const rect = c.getBoundingClientRect();
    const mx = ev.clientX - rect.left;
    const my = ev.clientY - rect.top;

    // Solo dentro del área de dibujo
    if (mx < pad || mx > pad + w || my < pad || my > pad + h) {
      tip.style.display = 'none';
      return;
    }

    // índice de día más cercano
    const ratio = (mx - pad) / w;
    const i = Math.round(ratio * (n-1));
    const day = labels[i];

    // Valor acumulado (total) en ese día
    const total = totals[i];

    // Determinar en qué “capa” cayó el mouse (comparando con bandas)
    // Convertimos my a “valor” (inverso de yAt)
    const valAtY = (1 - ((my - pad) / (h - 10))) * maxV;
    let layerName = '';
    let layerVal = 0;

    // hitData está en orden de apilado: capa0 abajo, capaN arriba
    for (let k=0; k<hitData.length; k++) {
      const low = hitData[k].lower[i];
      const up  = hitData[k].upper[i];
      if (valAtY >= low && valAtY <= up) {
        layerName = hitData[k].name;
        layerVal = up - low;
        break;
      }
    }

    tip.style.display = 'block';
    tip.style.left = (ev.clientX + 12) + 'px';
    tip.style.top = (ev.clientY + 12) + 'px';
    tip.innerHTML = `
      <div><strong>${day}</strong></div>
      <div>Total WIP: <strong>${total}</strong></div>
      ${layerName ? `<div>${layerName}: <strong>${layerVal}</strong></div>` : ''}
      <div style="opacity:.85">(pasa el mouse para ver desglose).
    </div>
    `;
  }

  /*
              <p>
                    La línea negra marca el total de trabajo (suma de todas las columnas) por día.

                    Si esa línea sube: el sistema se está “llenando”.

                    Si baja: estás vaciando backlog/WIP (buen flujo).)
            </p>
  */

  function onLeave() { tip.style.display = 'none'; }

  // Evitar múltiples listeners si recargas parcial
  c.onmousemove = onMove;
  c.onmouseleave = onLeave;
}

drawStackedAreaCFD('cfdChart', cfdSeries);



</script>
