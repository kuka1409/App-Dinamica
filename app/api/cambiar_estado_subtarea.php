<?php
declare(strict_types=1);

/**
 * Archivo: cambiar_estado_subtarea.php
 *
 * Proposito:
 * Endpoint encargado de marcar una subtarea como completada o pendiente.
 *
 * Uso:
 * Se llama desde el frontend cuando el usuario cambia el estado de una subtarea
 * desde el listado o desde una tarjeta de tarea.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - id_subtarea {int}: ID de la subtarea que cambiara de estado.
 * - completada {boolean}: Nuevo estado de la subtarea.
 *
 * Salida:
 * - JSON de exito si el estado fue actualizado.
 * - JSON de error si la subtarea no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene usuario, datos JSON e ID de subtarea.
 * 4. Valida el booleano de estado.
 * 5. Comprueba propiedad de la subtarea.
 * 6. Actualiza el campo completada.
 * 7. Guarda el evento en auditoria.
 * 8. Responde al frontend.
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

// ID de la subtarea cuyo estado cambiara.
$idSubtarea = normalizar_entero_positivo($datos['id_subtarea'] ?? null, 'El ID de la subtarea');
// Nuevo estado de la subtarea.
$completada = normalizar_booleano($datos['completada'] ?? null, 'El estado de la subtarea');

/* ============================================================
   Verificacion y cambio de estado
   ============================================================ */

try {
    // Comprueba que el usuario pueda modificar esta subtarea.
    if (!usuario_posee_subtarea($conexion, (int) $usuario['id'], $idSubtarea)) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    // Actualiza el estado usando JOIN para proteger la propiedad del registro.
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

    // Registra si la subtarea quedo completada o pendiente.
    registrar_log($conexion, (int) $usuario['id'], 'actualizacion', 'subtareas', $idSubtarea, $completada ? 'Subtarea completada' : 'Subtarea marcada como pendiente');
    // Respuesta final para el frontend.
    respuesta_exitosa(['mensaje' => 'Estado actualizado correctamente.']);
} catch (Throwable $excepcion) {
    // Respuesta generica en caso de error interno.
    respuesta_error('No se pudo actualizar el estado.', 500);
}
