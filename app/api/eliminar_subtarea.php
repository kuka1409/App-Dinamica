<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');
$usuario = usuario_api();
$datos = obtener_entrada_json();
$idSubtarea = normalizar_entero_positivo($datos['id_subtarea'] ?? null, 'El ID de la subtarea');

try {
    if (!usuario_posee_subtarea($conexion, (int) $usuario['id'], $idSubtarea)) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    $consulta = $conexion->prepare(
        'DELETE s
         FROM subtareas s
         INNER JOIN tareas t ON t.id = s.id_tarea
         WHERE s.id = :id AND t.id_usuario = :id_usuario'
    );
    $consulta->execute([
        ':id' => $idSubtarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    registrar_log($conexion, (int) $usuario['id'], 'eliminacion', 'Eliminar registro', 'subtareas', $idSubtarea, 'Subtarea eliminada');
    respuesta_exitosa(['mensaje' => 'Subtarea eliminada correctamente.']);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo eliminar la subtarea.', 500);
}
