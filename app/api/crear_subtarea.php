<?php
declare(strict_types=1);

/**
 * Archivo: crear_subtarea.php
 *
 * Proposito:
 * Endpoint encargado de crear una subtarea asociada a una tarea del usuario.
 *
 * Uso:
 * Se llama desde el frontend cuando el usuario envia el formulario de creacion
 * de subtareas.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - id_tarea {int}: ID de la tarea principal.
 * - titulo {string}: Titulo de la nueva subtarea.
 * - descripcion {string|null}: Descripcion opcional.
 *
 * Salida:
 * - JSON de exito con el ID de la subtarea creada.
 * - JSON de error si la tarea no existe o no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene usuario y datos JSON.
 * 4. Valida ID de tarea, titulo y descripcion.
 * 5. Comprueba que la tarea pertenezca al usuario.
 * 6. Calcula la siguiente posicion de la subtarea.
 * 7. Inserta la subtarea en base de datos.
 * 8. Registra la accion en auditoria.
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

// Asegura que este endpoint solo acepte peticiones POST.
requerir_metodo('POST');
// Usuario autenticado que realiza la accion.
$usuario = usuario_api();
// Datos enviados por el frontend en formato JSON.
$datos = obtener_entrada_json();

/* ============================================================
   Normalizacion y validacion de datos
   ============================================================ */

// ID de la tarea padre donde se creara la subtarea.
$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');
// Titulo de la subtarea.
$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 160, 'El título de la subtarea');
// Descripcion opcional de la subtarea.
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la subtarea');

/* ============================================================
   Creacion del registro
   ============================================================ */

try {
    // Verifica que la tarea padre pertenezca al usuario.
    if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea seleccionada no existe o no pertenece a tu cuenta.', 404);
    }

    // Calcula la siguiente posicion para ordenar las subtareas de la tarea.
    $consultaPosicion = $conexion->prepare('SELECT COALESCE(MAX(posicion), 0) + 1 FROM subtareas WHERE id_tarea = :id_tarea');
    $consultaPosicion->execute([':id_tarea' => $idTarea]);
    // Posicion final que se guardara en la nueva subtarea.
    $posicion = (int) $consultaPosicion->fetchColumn();

    // Consulta preparada que inserta la subtarea.
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

    // ID generado automaticamente por MySQL para la subtarea nueva.
    $idSubtarea = (int) $conexion->lastInsertId();
    // Guarda evidencia de la creacion en auditoria.
    registrar_log($conexion, (int) $usuario['id'], 'creacion', 'subtareas', $idSubtarea, 'Subtarea creada: ' . $titulo . ' | Tarea relacionada: ' . $idTarea);

    // Devuelve al frontend el ID de la subtarea creada.
    respuesta_exitosa([
        'mensaje' => 'Subtarea creada correctamente.',
        'id_subtarea' => $idSubtarea,
    ], 201);
} catch (Throwable $excepcion) {
    // Respuesta generica si ocurre un error al crear la subtarea.
    respuesta_error('No se pudo crear la subtarea.', 500);
}
