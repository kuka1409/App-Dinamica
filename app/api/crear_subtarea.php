<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');
$usuario = usuario_api();
$datos = obtener_entrada_json();

$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');
$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 160, 'El título de la subtarea');
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la subtarea');

try {
    if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea seleccionada no existe o no pertenece a tu cuenta.', 404);
    }

    $consultaPosicion = $conexion->prepare('SELECT COALESCE(MAX(posicion), 0) + 1 FROM subtareas WHERE id_tarea = :id_tarea');
    $consultaPosicion->execute([':id_tarea' => $idTarea]);
    $posicion = (int) $consultaPosicion->fetchColumn();

    $consulta = $conexion->prepare(
        'INSERT INTO subtareas (id_tarea, titulo, descripcion, posicion)
         VALUES (:id_tarea, :titulo, :descripcion, :posicion)'
    );
    $consulta->execute([
        ':id_tarea' => $idTarea,
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':posicion' => $posicion,
    ]);

    $idSubtarea = (int) $conexion->lastInsertId();
    registrar_log($conexion, (int) $usuario['id'], 'creacion', 'Crear registro', 'subtareas', $idSubtarea, 'Subtarea creada: ' . $titulo . ' | Tarea relacionada: ' . $idTarea);

    respuesta_exitosa([
        'mensaje' => 'Subtarea creada correctamente.',
        'id_subtarea' => $idSubtarea,
    ], 201);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo crear la subtarea.', 500);
}
