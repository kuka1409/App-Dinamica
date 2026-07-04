<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');
$usuario = usuario_api();
$datos = obtener_entrada_json();

$idSubtarea = normalizar_entero_positivo($datos['id_subtarea'] ?? null, 'El ID de la subtarea');
$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');
$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 160, 'El título de la subtarea');
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la subtarea');
$completada = normalizar_booleano($datos['completada'] ?? false, 'El estado de la subtarea');

try {
    if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea seleccionada no existe o no pertenece a tu cuenta.', 404);
    }

    if (!usuario_posee_subtarea($conexion, (int) $usuario['id'], $idSubtarea)) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    $consulta = $conexion->prepare(
        'UPDATE subtareas s
         INNER JOIN tareas t ON t.id = s.id_tarea
         SET s.id_tarea = :id_tarea,
             s.titulo = :titulo,
             s.descripcion = :descripcion,
             s.completada = :completada
         WHERE s.id = :id AND t.id_usuario = :id_usuario'
    );
    $consulta->execute([
        ':id_tarea' => $idTarea,
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':completada' => $completada ? 1 : 0,
        ':id' => $idSubtarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    registrar_log($conexion, (int) $usuario['id'], 'actualizacion', 'Modificar registro', 'subtareas', $idSubtarea, 'Subtarea actualizada: ' . $titulo . ' | Tarea relacionada: ' . $idTarea);
    respuesta_exitosa(['mensaje' => 'Subtarea actualizada correctamente.']);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo actualizar la subtarea.', 500);
}
