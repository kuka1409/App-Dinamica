<?php
declare(strict_types=1);

/**
 * Archivo: eliminar_subtarea.php
 *
 * Proposito:
 * Endpoint encargado de eliminar una subtarea del usuario autenticado.
 *
 * Uso:
 * Se llama desde el frontend cuando el usuario confirma la eliminacion de una
 * subtarea.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - id_subtarea {int}: ID de la subtarea que se quiere eliminar.
 *
 * Salida:
 * - JSON de exito si la subtarea se elimina correctamente.
 * - JSON de error si la subtarea no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene usuario, JSON e ID de subtarea.
 * 4. Verifica que la subtarea pertenezca al usuario.
 * 5. Elimina la subtarea usando una consulta protegida por usuario.
 * 6. Registra el movimiento en auditoria.
 * 7. Devuelve respuesta JSON.
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

// ID de la subtarea que se eliminara.
$idSubtarea = normalizar_entero_positivo($datos['id_subtarea'] ?? null, 'El ID de la subtarea');

/* ============================================================
   Verificacion y eliminacion
   ============================================================ */

try {
    // Impide eliminar subtareas de otra cuenta.
    if (!usuario_posee_subtarea($conexion, (int) $usuario['id'], $idSubtarea)) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    // Consulta preparada con JOIN para borrar solo subtareas del usuario.
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

    // Registra la eliminacion en auditoria.
    registrar_log($conexion, (int) $usuario['id'], 'eliminacion', 'subtareas', $idSubtarea, 'Subtarea eliminada');
    // Devuelve respuesta JSON al frontend.
    respuesta_exitosa(['mensaje' => 'Subtarea eliminada correctamente.']);
} catch (Throwable $excepcion) {
    // Respuesta generica si la eliminacion falla.
    respuesta_error('No se pudo eliminar la subtarea.', 500);
}
