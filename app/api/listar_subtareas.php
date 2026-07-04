<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('GET');
$usuario = usuario_api();
$idTarea = isset($_GET['id_tarea']) && $_GET['id_tarea'] !== ''
    ? normalizar_entero_positivo($_GET['id_tarea'], 'El ID de la tarea')
    : null;

try {
    $parametros = [':id_usuario' => (int) $usuario['id']];
    $condicionTarea = '';

    if ($idTarea !== null) {
        if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
            respuesta_error('La tarea seleccionada no existe o no pertenece a tu cuenta.', 404);
        }

        $condicionTarea = ' AND s.id_tarea = :id_tarea';
        $parametros[':id_tarea'] = $idTarea;
    }

    $consulta = $conexion->prepare(
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
         WHERE t.id_usuario = :id_usuario' . $condicionTarea . '
         ORDER BY t.fecha_actualizacion DESC, s.posicion ASC, s.id ASC'
    );
    $consulta->execute($parametros);

    $subtareas = array_map('formatear_subtarea', $consulta->fetchAll());
    registrar_log(
        $conexion,
        (int) $usuario['id'],
        'lectura',
        'Consultar registro',
        'subtareas',
        $idTarea !== null ? 'tarea_' . $idTarea : null,
        $idTarea !== null ? 'Consulta de subtareas asociadas a la tarea ' . $idTarea : 'Consulta de listado de subtareas'
    );
    respuesta_exitosa(['subtareas' => $subtareas]);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudieron cargar las subtareas.', 500);
}
