<?php
declare(strict_types=1);

/**
 * Archivo: eliminar_tarea.php
 *
 * Proposito:
 * Endpoint encargado de eliminar una tarea del usuario autenticado.
 *
 * Uso:
 * Se llama desde el frontend cuando el usuario confirma la eliminacion de una
 * tarea.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - id_tarea {int}: ID de la tarea que se quiere eliminar.
 *
 * Salida:
 * - JSON de exito si la tarea se elimina correctamente.
 * - JSON de error si la tarea no existe o no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene usuario, datos JSON e ID de tarea.
 * 4. Verifica propiedad de la tarea.
 * 5. Obtiene los IDs de subtareas asociadas para auditoria.
 * 6. Elimina la tarea.
 * 7. Registra la eliminacion de la tarea y sus subtareas.
 * 8. Devuelve respuesta JSON.
 */

/* ============================================================
   Carga de dependencias
   ============================================================ */

// Carga helpers compartidos para respuestas JSON, sesion, validaciones y logs.
require_once __DIR__ . '/comun.php';

/* ============================================================
   Validacion inicial de la peticion
   ============================================================ */

// Asegura que este endpoint solo acepte peticiones POST.
requerir_metodo('POST');
// Usuario autenticado que realiza la accion.
$usuario = usuario_api();
// Datos enviados por el frontend en formato JSON.
$datos = obtener_entrada_json();
/* ============================================================
   Normalizacion y validacion de datos
   ============================================================ */

// ID de la tarea que se eliminara.
$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');

/* ============================================================
   Verificacion y eliminacion
   ============================================================ */

try {
    // Impide eliminar tareas de otro usuario.
    if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea no existe o no pertenece a tu cuenta.', 404);
    }

    // Obtiene subtareas asociadas antes de borrar la tarea para dejar registro en logs.
    $consultaSubtareas = $conexion->prepare('SELECT id FROM subtareas WHERE id_tarea = :id_tarea ORDER BY id ASC');
    $consultaSubtareas->execute([':id_tarea' => $idTarea]);
    // Lista de IDs de subtareas que desapareceran por la eliminacion en cascada.
    $idsSubtareas = array_map('strval', $consultaSubtareas->fetchAll(PDO::FETCH_COLUMN));

    // Elimina la tarea solo si pertenece al usuario actual.
    $consulta = $conexion->prepare('DELETE FROM tareas WHERE id = :id AND id_usuario = :id_usuario');
    $consulta->execute([
        ':id' => $idTarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    // Registra la eliminacion de la tarea.
    registrar_log($conexion, (int) $usuario['id'], 'eliminacion', 'tareas', $idTarea, 'Tarea eliminada');

    // Si la tarea tenia subtareas, se deja un log adicional con sus IDs.
    if (count($idsSubtareas) > 0) {
        registrar_log(
            $conexion,
            (int) $usuario['id'],
            'eliminacion',
            'subtareas',
            'tarea_' . $idTarea,
            'Subtareas eliminadas por eliminación de tarea. IDs: ' . implode(', ', $idsSubtareas)
        );
    }
    // Devuelve respuesta JSON al frontend.
    respuesta_exitosa(['mensaje' => 'Tarea eliminada correctamente.']);
} catch (Throwable $excepcion) {
    // Respuesta generica si ocurre un error interno.
    respuesta_error('No se pudo eliminar la tarea.', 500);
}
