<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');
$usuario = usuario_api();
$datos = obtener_entrada_json();
$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');

try {
    if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea no existe o no pertenece a tu cuenta.', 404);
    }

    $consultaSubtareas = $conexion->prepare('SELECT id FROM subtareas WHERE id_tarea = :id_tarea ORDER BY id ASC');
    $consultaSubtareas->execute([':id_tarea' => $idTarea]);
    $idsSubtareas = array_map('strval', $consultaSubtareas->fetchAll(PDO::FETCH_COLUMN));

    $consulta = $conexion->prepare('DELETE FROM tareas WHERE id = :id AND id_usuario = :id_usuario');
    $consulta->execute([
        ':id' => $idTarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    registrar_log($conexion, (int) $usuario['id'], 'eliminacion', 'Eliminar registro', 'tareas', $idTarea, 'Tarea eliminada');

    if (count($idsSubtareas) > 0) {
        registrar_log(
            $conexion,
            (int) $usuario['id'],
            'eliminacion',
            'Eliminar registro',
            'subtareas',
            'tarea_' . $idTarea,
            'Subtareas eliminadas por eliminación de tarea. IDs: ' . implode(', ', $idsSubtareas)
        );
    }
    respuesta_exitosa(['mensaje' => 'Tarea eliminada correctamente.']);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo eliminar la tarea.', 500);
}
