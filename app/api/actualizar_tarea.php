<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');
$usuario = usuario_api();
$datos = obtener_entrada_json();

$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');
$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 120, 'El título de la tarea');
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la tarea');

try {
    $consulta = $conexion->prepare(
        'UPDATE tareas
         SET titulo = :titulo, descripcion = :descripcion
         WHERE id = :id AND id_usuario = :id_usuario'
    );
    $consulta->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':id' => $idTarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    if ($consulta->rowCount() === 0 && !usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea no existe o no pertenece a tu cuenta.', 404);
    }

    registrar_log($conexion, (int) $usuario['id'], 'actualizacion', 'Modificar registro', 'tareas', $idTarea, 'Tarea actualizada: ' . $titulo);
    respuesta_exitosa(['mensaje' => 'Tarea actualizada correctamente.']);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo actualizar la tarea.', 500);
}
