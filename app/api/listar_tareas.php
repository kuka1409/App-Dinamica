<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('GET');
$usuario = usuario_api();

try {
    $consulta = $conexion->prepare(
        'SELECT
            t.id,
            t.titulo,
            t.descripcion,
            t.fecha_creacion,
            t.fecha_actualizacion,
            COUNT(s.id) AS total_subtareas,
            COALESCE(SUM(CASE WHEN s.completada = 1 THEN 1 ELSE 0 END), 0) AS subtareas_completadas
         FROM tareas t
         LEFT JOIN subtareas s ON s.id_tarea = t.id
         WHERE t.id_usuario = :id_usuario
         GROUP BY t.id, t.titulo, t.descripcion, t.fecha_creacion, t.fecha_actualizacion
         ORDER BY t.fecha_actualizacion DESC, t.id DESC'
    );
    $consulta->execute([':id_usuario' => (int) $usuario['id']]);

    $tareas = array_map('formatear_tarea', $consulta->fetchAll());

    if (count($tareas) === 0) {
        registrar_log($conexion, (int) $usuario['id'], 'lectura', 'Consultar registro', 'tareas', null, 'Consulta de listado de tareas sin resultados');
        respuesta_exitosa(['tareas' => []]);
    }

    $tareasPorId = [];
    foreach ($tareas as $indice => $tarea) {
        $tareas[$indice]['subtareas'] = [];
        $tareasPorId[(int) $tarea['id']] = $indice;
    }

    $idsTareas = array_keys($tareasPorId);
    $marcadores = implode(',', array_fill(0, count($idsTareas), '?'));

    $consultaSubtareas = $conexion->prepare(
        'SELECT
            s.id,
            s.id_tarea,
            t.titulo AS titulo_tarea,
            s.titulo,
            s.descripcion,
            s.completada,
            s.posicion,
            s.fecha_creacion,
            s.fecha_actualizacion
         FROM subtareas s
         INNER JOIN tareas t ON t.id = s.id_tarea
         WHERE t.id_usuario = ? AND s.id_tarea IN (' . $marcadores . ')
         ORDER BY s.id_tarea ASC, s.completada ASC, s.posicion ASC, s.id ASC'
    );
    $consultaSubtareas->execute(array_merge([(int) $usuario['id']], $idsTareas));

    foreach ($consultaSubtareas->fetchAll() as $filaSubtarea) {
        $idTarea = (int) $filaSubtarea['id_tarea'];

        if (!array_key_exists($idTarea, $tareasPorId)) {
            continue;
        }

        $indiceTarea = $tareasPorId[$idTarea];
        $tareas[$indiceTarea]['subtareas'][] = formatear_subtarea($filaSubtarea);
    }

    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'Consultar registro', 'tareas', null, 'Consulta de listado de tareas');
    respuesta_exitosa(['tareas' => $tareas]);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudieron cargar las tareas.', 500);
}
