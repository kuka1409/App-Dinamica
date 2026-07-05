<?php
declare(strict_types=1);

/**
 * Archivo: crear_tarea.php
 *
 * Proposito:
 * Endpoint encargado de crear una nueva tarea para el usuario autenticado.
 *
 * Uso:
 * Se llama desde el frontend cuando el usuario envia el formulario de creacion
 * de tareas.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - titulo {string}: Titulo principal de la tarea.
 * - descripcion {string|null}: Descripcion opcional.
 *
 * Salida:
 * - JSON de exito con el ID de la tarea creada.
 * - JSON de error si falta informacion o ocurre un problema.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene el usuario autenticado y el JSON recibido.
 * 4. Valida titulo y descripcion.
 * 5. Inserta la tarea en base de datos.
 * 6. Registra la accion en auditoria.
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

// Titulo obligatorio de la nueva tarea.
$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 120, 'El título de la tarea');
// Descripcion opcional de la tarea.
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la tarea');

/* ============================================================
   Creacion del registro
   ============================================================ */

try {
    // Consulta preparada que crea la tarea para el usuario actual.
    $consulta = $conexion->prepare(
        'INSERT INTO tareas (id_usuario, titulo, descripcion)
         VALUES (:id_usuario, :titulo, :descripcion)'
    );
    $consulta->execute([
        ':id_usuario' => (int) $usuario['id'],
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
    ]);

    // ID generado automaticamente por MySQL para la tarea nueva.
    $idTarea = (int) $conexion->lastInsertId();
    // Registra la creacion en auditoria.
    registrar_log($conexion, (int) $usuario['id'], 'creacion', 'tareas', $idTarea, 'Tarea creada: ' . $titulo);

    // Devuelve al frontend el ID de la tarea creada.
    respuesta_exitosa([
        'mensaje' => 'Tarea creada correctamente.',
        'id_tarea' => $idTarea,
    ], 201);
} catch (Throwable $excepcion) {
    // Respuesta generica si ocurre un error interno.
    respuesta_error('No se pudo crear la tarea.', 500);
}
