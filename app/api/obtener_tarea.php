<?php
declare(strict_types=1);

/**
 * Archivo: obtener_tarea.php
 *
 * Proposito:
 * Endpoint encargado de obtener una tarea especifica del usuario autenticado.
 *
 * Uso:
 * Se llama desde el frontend cuando se necesita cargar datos para ver, editar
 * o eliminar una tarea.
 *
 * Metodo esperado:
 * - GET
 *
 * Entrada esperada:
 * - id {int}: ID de la tarea solicitada.
 *
 * Salida:
 * - JSON con la tarea formateada.
 * - JSON de error si la tarea no existe o no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige GET y usuario autenticado.
 * 3. Valida el ID recibido por query string.
 * 4. Consulta la tarea y calcula resumen de subtareas.
 * 5. Verifica que exista resultado.
 * 6. Registra la consulta en auditoria.
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

// Asegura que este endpoint solo acepte peticiones GET.
requerir_metodo('GET');
// Usuario autenticado que realiza la accion.
$usuario = usuario_api();
/* ============================================================
   Lectura y validacion de parametros
   ============================================================ */

// ID recibido por query string para buscar una tarea concreta.
$idTarea = normalizar_entero_positivo($_GET['id'] ?? null, 'El ID de la tarea');

/* ============================================================
   Consulta de la tarea
   ============================================================ */

try {
    // Busca la tarea y calcula cantidad de subtareas.
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

    // Fila encontrada en base de datos, o false si no existe.
    $tarea = $consulta->fetch();

    // Si no hay resultado, la tarea no existe o no pertenece al usuario.
    if (!$tarea) {
        respuesta_error('La tarea no existe o no pertenece a tu cuenta.', 404);
    }

    // Registra la lectura de la tarea.
    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'tareas', $idTarea, 'Consulta de tarea: ' . (string) $tarea['titulo']);
    // Devuelve la tarea formateada.
    respuesta_exitosa(['tarea' => formatear_tarea($tarea)]);
} catch (Throwable $excepcion) {
    // Respuesta generica si falla la consulta.
    respuesta_error('No se pudo cargar la tarea.', 500);
}
