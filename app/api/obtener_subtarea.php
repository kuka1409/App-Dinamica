<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('GET');
$usuario = usuario_api();
$idSubtarea = normalizar_entero_positivo($_GET['id'] ?? null, 'El ID de la subtarea');

try {
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
         WHERE s.id = :id AND t.id_usuario = :id_usuario
         LIMIT 1'
    );
    $consulta->execute([
        ':id' => $idSubtarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    $subtarea = $consulta->fetch();

    if (!$subtarea) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'Consultar registro', 'subtareas', $idSubtarea, 'Consulta de subtarea: ' . (string) $subtarea['titulo']);
    respuesta_exitosa(['subtarea' => formatear_subtarea($subtarea)]);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo cargar la subtarea.', 500);
}
