<?php
declare(strict_types=1);

/**
 * Archivo: listar_subtareas.php
 *
 * Proposito:
 * Endpoint encargado de listar subtareas del usuario autenticado.
 *
 * Uso:
 * Se llama desde el frontend para cargar subtareas generales o subtareas
 * asociadas a una tarea especifica.
 *
 * Metodo esperado:
 * - GET
 *
 * Entrada esperada:
 * - id_tarea {int|null}: ID opcional para filtrar por una tarea concreta.
 *
 * Salida:
 * - JSON con las subtareas formateadas.
 * - JSON de error si la tarea filtrada no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige GET y usuario autenticado.
 * 3. Lee el filtro opcional de tarea.
 * 4. Verifica propiedad de la tarea si se uso filtro.
 * 5. Consulta subtareas usando INNER JOIN con tareas.
 * 6. Formatea cada subtarea para el frontend.
 * 7. Registra la lectura en auditoria.
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

// Asegura que este endpoint solo acepte peticiones GET.
requerir_metodo('GET');
// Usuario autenticado que realiza la accion.
$usuario = usuario_api();
/* ============================================================
   Lectura de filtro opcional
   ============================================================ */

// ID opcional para listar solo subtareas de una tarea especifica.
$idTarea = isset($_GET['id_tarea']) && $_GET['id_tarea'] !== ''
    ? normalizar_entero_positivo($_GET['id_tarea'], 'El ID de la tarea')
    : null;

/* ============================================================
   Consulta de subtareas
   ============================================================ */

try {
    // Parametros SQL iniciales. Siempre se filtra por usuario.
    $parametros = [':id_usuario' => (int) $usuario['id']];
    // Condicion adicional que se agrega solo si existe filtro por tarea.
    $condicionTarea = '';

    // Si llega id_tarea, se valida que esa tarea pertenezca al usuario.
    if ($idTarea !== null) {
        if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
            respuesta_error('La tarea seleccionada no existe o no pertenece a tu cuenta.', 404);
        }

        $condicionTarea = ' AND s.id_tarea = :id_tarea';
        $parametros[':id_tarea'] = $idTarea;
    }

    // Consulta subtareas junto con el titulo de su tarea padre.
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
         WHERE t.id_usuario = :id_usuario' . $condicionTarea . '
         ORDER BY t.fecha_actualizacion DESC, s.posicion ASC, s.id ASC'
    );
    $consulta->execute($parametros);

    // Convierte cada fila SQL en el formato esperado por el frontend.
    $subtareas = array_map('formatear_subtarea', $consulta->fetchAll());
    // Registra la lectura de subtareas en auditoria.
    registrar_log(
        $conexion,
        (int) $usuario['id'],
        'lectura',
        'subtareas',
        $idTarea !== null ? 'tarea_' . $idTarea : null,
        $idTarea !== null ? 'Consulta de subtareas asociadas a la tarea ' . $idTarea : 'Consulta de listado de subtareas'
    );
    // Devuelve la lista al frontend.
    respuesta_exitosa(['subtareas' => $subtareas]);
} catch (Throwable $excepcion) {
    // Respuesta generica si falla la consulta.
    respuesta_error('No se pudieron cargar las subtareas.', 500);
}
