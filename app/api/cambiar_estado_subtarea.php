<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');
$usuario = usuario_api();
$datos = obtener_entrada_json();
$idSubtarea = normalizar_entero_positivo($datos['id_subtarea'] ?? null, 'El ID de la subtarea');
$completada = normalizar_booleano($datos['completada'] ?? null, 'El estado de la subtarea');

try {
    if (!usuario_posee_subtarea($conexion, (int) $usuario['id'], $idSubtarea)) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    $consulta = $conexion->prepare(
        'UPDATE subtareas s
         INNER JOIN tareas t ON t.id = s.id_tarea
         SET s.completada = :completada
         WHERE s.id = :id AND t.id_usuario = :id_usuario'
    );
    $consulta->execute([
        ':completada' => $completada ? 1 : 0,
        ':id' => $idSubtarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    registrar_log($conexion, (int) $usuario['id'], 'actualizacion', 'Modificar registro', 'subtareas', $idSubtarea, $completada ? 'Subtarea completada' : 'Subtarea marcada como pendiente');
    respuesta_exitosa(['mensaje' => 'Estado actualizado correctamente.']);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo actualizar el estado.', 500);
}
