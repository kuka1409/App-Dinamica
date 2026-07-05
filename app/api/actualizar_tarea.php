<?php
declare(strict_types=1);

/**
 * Archivo: actualizar_tarea.php
 *
 * Proposito:
 * Endpoint encargado de actualizar una tarea existente del usuario autenticado.
 *
 * Uso:
 * Se llama desde el frontend cuando el usuario envia el formulario de edicion
 * de tareas.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - id_tarea {int}: ID de la tarea que se quiere modificar.
 * - titulo {string}: Nuevo titulo de la tarea.
 * - descripcion {string|null}: Nueva descripcion opcional.
 *
 * Salida:
 * - JSON de exito si la tarea se actualiza correctamente.
 * - JSON de error si faltan datos o si la tarea no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene el usuario autenticado y los datos recibidos.
 * 4. Valida el ID, el titulo y la descripcion.
 * 5. Ejecuta una consulta UPDATE limitada al usuario actual.
 * 6. Verifica si la tarea existe cuando no hubo cambios.
 * 7. Registra el movimiento en auditoria.
 * 8. Devuelve una respuesta JSON.
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

// ID de la tarea que se quiere actualizar.
$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');
// Titulo nuevo de la tarea.
$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 120, 'El título de la tarea');
// Descripcion opcional de la tarea.
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la tarea');

/* ============================================================
   Actualizacion en base de datos
   ============================================================ */

try {
    // Consulta preparada que actualiza solo la tarea del usuario actual.
    $consulta = $conexion->prepare(
        'UPDATE tareas
         SET titulo = :titulo, descripcion = :descripcion
         WHERE id = :id AND id_usuario = :id_usuario'
    );
    $consulta->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':id' => $idTarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    // Si no hubo cambios, se verifica si la tarea realmente existe.
    if ($consulta->rowCount() === 0 && !usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea no existe o no pertenece a tu cuenta.', 404);
    }

    // Registra la modificacion para auditoria.
    registrar_log($conexion, (int) $usuario['id'], 'actualizacion', 'tareas', $idTarea, 'Tarea actualizada: ' . $titulo);
    // Devuelve respuesta JSON al frontend.
    respuesta_exitosa(['mensaje' => 'Tarea actualizada correctamente.']);
} catch (Throwable $excepcion) {
    // Evita exponer detalles internos al usuario final.
    respuesta_error('No se pudo actualizar la tarea.', 500);
}
