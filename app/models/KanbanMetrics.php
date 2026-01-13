<?php
require_once __DIR__ . '/../../core/Model.php';

class KanbanMetrics extends Model
{
    public static function projectReport(int $projectId): array
    {
        $tz = new DateTimeZone('America/Bogota');
        $now = new DateTime('now', $tz);


        $db = self::db();

        // Columnas del proyecto
        $stmt = $db->prepare("SELECT id, name, is_done, position FROM columns WHERE project_id = :pid ORDER BY position ASC");
        $stmt->execute(['pid' => $projectId]);
        $columns = $stmt->fetchAll();

        // Tareas del proyecto
        $stmt = $db->prepare("SELECT id, title, responsible, created_at, completed_at, column_id FROM tasks WHERE project_id = :pid");
        $stmt->execute(['pid' => $projectId]);
        $tasks = $stmt->fetchAll();

        // Movimientos del proyecto (ordenados)
        $stmt = $db->prepare("
          SELECT task_id, from_column_id, to_column_id, moved_at
          FROM task_movements
          WHERE project_id = :pid
          ORDER BY task_id ASC, moved_at ASC
        ");
        $stmt->execute(['pid' => $projectId]);
        $moves = $stmt->fetchAll();

        // Agrupar movimientos por tarea
        $movesByTask = [];
        foreach ($moves as $m) {
            $movesByTask[$m['task_id']][] = $m;
        }

        // Mapa columnas
        $colMap = [];
        foreach ($columns as $c) $colMap[$c['id']] = $c;

        // $now = new DateTime();

        // Tiempo por columna por tarea (en horas)
        $taskTimes = [];
        $colTotalsHours = array_fill_keys(array_column($columns, 'id'), 0.0);

        $cycleTimesHours = [];   // created_at -> completed_at (si está done)
        $throughputByDay = [];   // YYYY-MM-DD -> count completadas

        foreach ($tasks as $t) {
            $tid = (int)$t['id'];
            $history = $movesByTask[$tid] ?? [];

            // Construir segmentos: [colId, enterTime, exitTime]
            // Usamos: el primer movimiento define la entrada inicial (NULL -> to_column)
            // y cada movimiento posterior define salida de from_column y entrada a to_column.
            $segments = [];

            // Si no hay movimientos, asumimos que entró a su columna actual en created_at
            if (empty($history)) {
                $enter = new DateTime($t['created_at']);
                $exit  = $t['completed_at'] ? new DateTime($t['completed_at']) : $now;
                $segments[] = ['col' => (int)$t['column_id'], 'in' => $enter, 'out' => $exit];
            } else {
                // Entrada inicial
                $first = $history[0];
                $enterCol = (int)$first['to_column_id'];
                $enterAt  = new DateTime($first['moved_at'], $tz);

                // Recorremos movimientos y cerramos segmentos
                for ($i = 1; $i < count($history); $i++) {
                    $prev = $history[$i - 1];
                    $cur  = $history[$i];

                    $fromCol = (int)$cur['from_column_id'];
                    $toCol   = (int)$cur['to_column_id'];
                    $movedAt = new DateTime($cur['moved_at']);

                    // Segmento: estaba en $fromCol desde el tiempo del movimiento anterior (entrada)
                    // La entrada a fromCol ocurrió cuando el movimiento previo lo puso ahí:
                    // es decir, el "to_column_id" del prev.
                    $segCol = (int)$prev['to_column_id'];
                    $segIn  = new DateTime($prev['moved_at']);
                    $segOut = $movedAt;
                    $segments[] = ['col' => $segCol, 'in' => $segIn, 'out' => $segOut];

                    // continúa…
                }

                // Último segmento: desde último moved_at hasta ahora o completed_at
                $last = $history[count($history)-1];
                $lastCol = (int)$last['to_column_id'];
                $lastIn  = new DateTime($last['moved_at']);
                $lastOut = $t['completed_at'] ? new DateTime($t['completed_at']) : $now;
                $segments[] = ['col' => $lastCol, 'in' => $lastIn, 'out' => $lastOut];
            }

            $timesByCol = [];
            foreach ($segments as $s) {
                $colId = (int)$s['col'];
                if (!isset($colMap[$colId])) continue;

                $diff = $s['out']->getTimestamp() - $s['in']->getTimestamp();
                if ($diff < 0) $diff = 0;
                $hours = $diff / 3600;

                $timesByCol[$colId] = ($timesByCol[$colId] ?? 0) + $hours;
                $colTotalsHours[$colId] += $hours;
            }

            // Cycle time simple: created_at -> completed_at si completada
            if (!empty($t['completed_at'])) {
                $start = new DateTime($t['created_at']);
                $end   = new DateTime($t['completed_at']);
                $ctH = max(0, ($end->getTimestamp() - $start->getTimestamp()) / 3600);
                $cycleTimesHours[] = $ctH;

                $day = $end->format('Y-m-d');
                $throughputByDay[$day] = ($throughputByDay[$day] ?? 0) + 1;
            }

            $taskTimes[] = [
                'id' => $tid,
                'title' => $t['title'],
                'responsible' => $t['responsible'] ?? '',
                'times_by_col_hours' => $timesByCol,
                'created_at' => $t['created_at'],
                'completed_at' => $t['completed_at'] ?? null
            ];
        }

        // Promedio cycle time (horas)
        $avgCycle = !empty($cycleTimesHours) ? array_sum($cycleTimesHours) / count($cycleTimesHours) : 0;

        // Construir series para gráficas
        $colSeries = [];
        foreach ($columns as $c) {
            $cid = (int)$c['id'];
            $colSeries[] = [
                'column_id' => $cid,
                'name' => $c['name'],
                'total_hours' => round($colTotalsHours[$cid] ?? 0, 2)
            ];
        }

        ksort($throughputByDay);
        $throughputSeries = [];
        foreach ($throughputByDay as $day => $count) {
            $throughputSeries[] = ['day' => $day, 'count' => $count];
        }


        // ==============================
        // CFD + WIP + Aging + Cycle time real
        // ==============================

        // Detectar columnas "In Progress" (por nombre) y la columna Done (is_done=1)
        $inProgressColIds = [];
        $doneColIds = [];

        foreach ($columns as $c) {
            $name = mb_strtolower(trim($c['name']));
            if (!empty($c['is_done']) && (int)$c['is_done'] === 1) {
                $doneColIds[] = (int)$c['id'];
            }
            if (strpos($name, 'progreso') !== false || strpos($name, 'en progreso') !== false) {
                $inProgressColIds[] = (int)$c['id'];
            }
        }
        // fallback si no encuentra por nombre: usar segunda columna (si existe)
        if (empty($inProgressColIds) && count($columns) >= 2) {
            $inProgressColIds[] = (int)$columns[1]['id'];
        }

        // 1) WIP actual por columna (estado actual de tasks)
        $wipNow = [];
        foreach ($columns as $c) $wipNow[(int)$c['id']] = 0;

        foreach ($tasks as $t) {
            $cid = (int)$t['column_id'];
            if (isset($wipNow[$cid])) $wipNow[$cid]++;
        }

        // 2) Aging WIP: edad de cada tarea en su columna actual (horas/días)
        // Usamos la fecha del último movimiento (to_column_id) como "entered_at".
        // Si no hay movimientos, usamos created_at.
        $aging = [];
        foreach ($tasks as $t) {
            $tid = (int)$t['id'];
            $history = $movesByTask[$tid] ?? [];
            $enteredAt = !empty($history)
                ? new DateTime($history[count($history)-1]['moved_at'])
                : new DateTime($t['created_at']);

            $ageHours = max(0, ($now->getTimestamp() - $enteredAt->getTimestamp()) / 3600);
            $ageDays = $ageHours / 24;

            $aging[] = [
                'task_id' => $tid,
                'title' => $t['title'],
                'responsible' => $t['responsible'] ?? '',
                'column_id' => (int)$t['column_id'],
                'entered_at' => $enteredAt->format('Y-m-d H:i:s'),
                'age_hours' => round($ageHours, 2),
                'age_days' => round($ageDays, 2),
            ];
        }

        // Ordenar aging desc (más viejas primero) y tomar top 10
        usort($aging, fn($a, $b) => $b['age_hours'] <=> $a['age_hours']);
        $agingTop = array_slice($aging, 0, 10);

        // 3) Cycle time real: desde primera entrada a "En progreso" hasta entrada a "Hecho" (o completed_at)
        $cycleRealHours = [];
        foreach ($tasks as $t) {
            $tid = (int)$t['id'];
            $history = $movesByTask[$tid] ?? [];
            if (empty($history)) continue;

            $inProgAt = null;
            $doneAt = null;

            foreach ($history as $m) {
                $toCol = (int)$m['to_column_id'];
                $mAt = new DateTime($m['moved_at']);

                if ($inProgAt === null && in_array($toCol, $inProgressColIds, true)) {
                    $inProgAt = $mAt;
                }
                if ($doneAt === null && in_array($toCol, $doneColIds, true)) {
                    $doneAt = $mAt;
                }
            }

            // Si está completada pero no hay movimiento a done (casos raros), usar completed_at
            if ($doneAt === null && !empty($t['completed_at'])) {
                $doneAt = new DateTime($t['completed_at']);
            }

            if ($inProgAt && $doneAt) {
                $h = max(0, ($doneAt->getTimestamp() - $inProgAt->getTimestamp()) / 3600);
                $cycleRealHours[] = $h;
            }
        }

        $avgCycleReal = !empty($cycleRealHours) ? array_sum($cycleRealHours) / count($cycleRealHours) : 0;

        // 4) CFD (Cumulative Flow Diagram): conteo por columna por día
        // Rango: últimos 30 días (puedes cambiar a 60/90)
        $daysBack = 30;
        $start = (new DateTime())->modify("-{$daysBack} days")->setTime(0,0,0);
        $end   = (new DateTime())->setTime(0,0,0);

        // Construir lista de días
        $days = [];
        $cursor = clone $start;
        while ($cursor <= $end) {
            $days[] = $cursor->format('Y-m-d');
            $cursor->modify('+1 day');
        }

        // Para CFD, simulamos "estado al final del día".
        // 1) Estado inicial = columna actual, pero vamos a reconstruir desde movimientos.
        // Para hacerlo estable: partimos del estado de cada tarea al inicio del rango:
        // buscamos el último movimiento <= start, o si no, el primer movimiento/created_at.
        $cfd = []; // day -> [colId => count]
        foreach ($days as $d) {
            $cfd[$d] = [];
            foreach ($columns as $c) $cfd[$d][(int)$c['id']] = 0;
        }

        // Precalcular por tarea: lista de movimientos dentro/fuera del rango y el estado inicial
        foreach ($tasks as $t) {
            $tid = (int)$t['id'];
            $history = $movesByTask[$tid] ?? [];

            // Encontrar columna al inicio del rango
            $colAtStart = (int)$t['column_id']; // fallback
            $found = false;

            // Recorremos movimientos para hallar el último <= start
            foreach ($history as $m) {
                $mAt = new DateTime($m['moved_at']);
                if ($mAt <= $start) {
                    $colAtStart = (int)$m['to_column_id'];
                    $found = true;
                } else {
                    break;
                }
            }

            // Si no hay movimientos y la tarea se creó después del start, “aparece” desde su created_at
            $createdAt = !empty($t['created_at']) ? new DateTime($t['created_at']) : null;

            // Construir un puntero de movimientos por día
            $movesIdx = 0;
            // avanzar hasta primer movimiento >= start
            while ($movesIdx < count($history)) {
                $mAt = new DateTime($history[$movesIdx]['moved_at']);
                if ($mAt >= $start) break;
                $movesIdx++;
            }

            $currentCol = $colAtStart;

            // iterar por cada día y aplicar movimientos que ocurren ese día
            foreach ($days as $day) {
                $dayStart = new DateTime($day . ' 00:00:00');
                $dayEnd   = new DateTime($day . ' 23:59:59');

                // si la tarea aún no existía ese día, no cuenta
                if ($createdAt && $createdAt > $dayEnd) {
                    continue;
                }

                // aplicar todos los movimientos ocurridos en ese día
                while ($movesIdx < count($history)) {
                    $mAt = new DateTime($history[$movesIdx]['moved_at']);
                    if ($mAt >= $dayStart && $mAt <= $dayEnd) {
                        $currentCol = (int)$history[$movesIdx]['to_column_id'];
                        $movesIdx++;
                    } else {
                        if ($mAt > $dayEnd) break;
                        $movesIdx++;
                    }
                }

                // sumar conteo del día en la columna actual
                if (isset($cfd[$day][$currentCol])) {
                    $cfd[$day][$currentCol]++;
                }
            }
        }

        // Convertir CFD a series por columna: [{name, points:[{day,count}]}]
        $cfdSeries = [];
        foreach ($columns as $c) {
            $cid = (int)$c['id'];
            $points = [];
            foreach ($days as $d) {
                $points[] = ['day' => $d, 'count' => $cfd[$d][$cid] ?? 0];
            }
            $cfdSeries[] = [
                'column_id' => $cid,
                'name' => $c['name'],
                'points' => $points
            ];
        }

        // Empaquetar para la vista
        $wipSeries = [];
        foreach ($columns as $c) {
            $cid = (int)$c['id'];
            $wipSeries[] = ['column_id' => $cid, 'name' => $c['name'], 'count' => $wipNow[$cid] ?? 0];
        }




        return [
            'columns' => $columns,
            'taskTimes' => $taskTimes,
            'avgCycleHours' => round($avgCycle, 2),
            'colSeries' => $colSeries,
            'throughputSeries' => $throughputSeries,
            'avgCycleRealHours' => round($avgCycleReal, 2),
            'wipSeries' => $wipSeries,
            'agingTop' => $agingTop,
            'cfdSeries' => $cfdSeries,
            'cfdDays' => $days,

        ];
    }
}
