<?php
declare(strict_types=1);

/**
 * Archivo: listar_tareas.php
 *
 * Proposito:
 * Endpoint encargado de listar tareas del usuario autenticado junto con sus
 * subtareas principales.
 *
 * Uso:
 * Se llama desde el frontend para renderizar el listado principal de tareas.
 *
 * Metodo esperado:
 * - GET
 *
 * Entrada esperada:
 * - No requiere parametros.
 *
 * Salida:
 * - JSON con tareas formateadas y subtareas asociadas.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige GET y usuario autenticado.
 * 3. Consulta tareas con conteo de subtareas.
 * 4. Si no hay tareas, responde con lista vacia.
 * 5. Construye un mapa de tareas por ID.
 * 6. Consulta subtareas de todas las tareas encontradas.
 * 7. Inserta cada subtarea en su tarea correspondiente.
 * 8. Registra la lectura en auditoria.
 * 9. Devuelve respuesta JSON.
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
   Consulta principal de tareas
   ============================================================ */

try {
    // Consulta tareas del usuario y calcula resumen de subtareas.
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
         WHERE t.id_usuario = :id_usuario
         GROUP BY t.id, t.titulo, t.descripcion, t.fecha_creacion, t.fecha_actualizacion
         ORDER BY t.fecha_actualizacion DESC, t.id DESC'
    );
    $consulta->execute([':id_usuario' => (int) $usuario['id']]);

    // Formatea cada tarea para entregarla al frontend.
    $tareas = array_map('formatear_tarea', $consulta->fetchAll());

    // Si no hay tareas, responde rapido con un arreglo vacio.
    if (count($tareas) === 0) {
        // Registra la consulta del listado de tareas.
    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'tareas', null, 'Consulta de listado de tareas sin resultados');
        // Devuelve tareas con subtareas asociadas.
    respuesta_exitosa(['tareas' => []]);
    }

    // Mapa que permite ubicar rapido cada tarea por su ID.
    $tareasPorId = [];
    // Inicializa el arreglo de subtareas dentro de cada tarea.
    foreach ($tareas as $indice => $tarea) {
        $tareas[$indice]['subtareas'] = [];
        $tareasPorId[(int) $tarea['id']] = $indice;
    }

    // IDs de tareas usados para consultar sus subtareas en una sola consulta.
    $idsTareas = array_keys($tareasPorId);
    // Marcadores ? para construir de forma segura el IN (...) de SQL.
    $marcadores = implode(',', array_fill(0, count($idsTareas), '?'));

    // Consulta todas las subtareas de las tareas encontradas.
    $consultaSubtareas = $conexion->prepare(
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
         WHERE t.id_usuario = ? AND s.id_tarea IN (' . $marcadores . ')
         ORDER BY s.id_tarea ASC, s.completada ASC, s.posicion ASC, s.id ASC'
    );
    $consultaSubtareas->execute(array_merge([(int) $usuario['id']], $idsTareas));

    // Inserta cada subtarea dentro de la tarea que corresponde.
    foreach ($consultaSubtareas->fetchAll() as $filaSubtarea) {
        $idTarea = (int) $filaSubtarea['id_tarea'];

        if (!array_key_exists($idTarea, $tareasPorId)) {
            continue;
        }

        $indiceTarea = $tareasPorId[$idTarea];
        $tareas[$indiceTarea]['subtareas'][] = formatear_subtarea($filaSubtarea);
    }

    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'tareas', null, 'Consulta de listado de tareas');
    respuesta_exitosa(['tareas' => $tareas]);
} catch (Throwable $excepcion) {
    // Respuesta generica si falla la carga de tareas.
    respuesta_error('No se pudieron cargar las tareas.', 500);
}
