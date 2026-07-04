<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('GET');
$usuario = usuario_api();
$idTarea = normalizar_entero_positivo($_GET['id'] ?? null, 'El ID de la tarea');

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
         WHERE t.id = :id AND t.id_usuario = :id_usuario
         GROUP BY t.id, t.titulo, t.descripcion, t.fecha_creacion, t.fecha_actualizacion
         LIMIT 1'
    );
    $consulta->execute([
        ':id' => $idTarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    $tarea = $consulta->fetch();

    if (!$tarea) {
        respuesta_error('La tarea no existe o no pertenece a tu cuenta.', 404);
    }

    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'Consultar registro', 'tareas', $idTarea, 'Consulta de tarea: ' . (string) $tarea['titulo']);
    respuesta_exitosa(['tarea' => formatear_tarea($tarea)]);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo cargar la tarea.', 500);
}
