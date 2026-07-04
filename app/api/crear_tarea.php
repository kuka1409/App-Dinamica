<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');
$usuario = usuario_api();
$datos = obtener_entrada_json();

$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 120, 'El título de la tarea');
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la tarea');

try {
    $consulta = $conexion->prepare(
        'INSERT INTO tareas (id_usuario, titulo, descripcion)
         VALUES (:id_usuario, :titulo, :descripcion)'
    );
    $consulta->execute([
        ':id_usuario' => (int) $usuario['id'],
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
    ]);

    $idTarea = (int) $conexion->lastInsertId();
    registrar_log($conexion, (int) $usuario['id'], 'creacion', 'Crear registro', 'tareas', $idTarea, 'Tarea creada: ' . $titulo);

    respuesta_exitosa([
        'mensaje' => 'Tarea creada correctamente.',
        'id_tarea' => $idTarea,
    ], 201);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo crear la tarea.', 500);
}
