<?php
declare(strict_types=1);

/**
 * Archivo: actualizar_subtarea.php
 *
 * Proposito:
 * Endpoint encargado de modificar los datos de una subtarea existente.
 *
 * Uso:
 * Se llama desde el frontend cuando el usuario envia el formulario de edicion
 * de subtareas.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - id_subtarea {int}: ID de la subtarea que se quiere actualizar.
 * - id_tarea {int}: ID de la tarea a la que quedara asociada la subtarea.
 * - titulo {string}: Nuevo titulo de la subtarea.
 * - descripcion {string|null}: Nueva descripcion opcional.
 * - completada {boolean}: Estado de avance de la subtarea.
 *
 * Salida:
 * - JSON de exito si la subtarea se actualiza correctamente.
 * - JSON de error si la subtarea o la tarea no pertenecen al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene el usuario autenticado y los datos JSON.
 * 4. Valida IDs, textos y estado booleano.
 * 5. Comprueba que la tarea y la subtarea pertenezcan al usuario.
 * 6. Actualiza el registro en base de datos.
 * 7. Registra la accion en auditoria.
 * 8. Devuelve respuesta JSON al frontend.
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

// ID de la subtarea que se modificara.
$idSubtarea = normalizar_entero_positivo($datos['id_subtarea'] ?? null, 'El ID de la subtarea');
// ID de la tarea donde quedara asociada la subtarea.
$idTarea = normalizar_entero_positivo($datos['id_tarea'] ?? null, 'El ID de la tarea');
// Titulo nuevo de la subtarea.
$titulo = normalizar_texto_obligatorio($datos['titulo'] ?? null, 160, 'El título de la subtarea');
// Descripcion opcional de la subtarea.
$descripcion = normalizar_texto_opcional($datos['descripcion'] ?? null, 2000, 'La descripción de la subtarea');
// Estado final de la subtarea: completada o pendiente.
$completada = normalizar_booleano($datos['completada'] ?? false, 'El estado de la subtarea');

/* ============================================================
   Verificacion de propiedad y actualizacion
   ============================================================ */

try {
    // Evita asociar la subtarea a una tarea de otro usuario.
    if (!usuario_posee_tarea($conexion, (int) $usuario['id'], $idTarea)) {
        respuesta_error('La tarea seleccionada no existe o no pertenece a tu cuenta.', 404);
    }

    // Evita actualizar una subtarea que pertenece a otra cuenta.
    if (!usuario_posee_subtarea($conexion, (int) $usuario['id'], $idSubtarea)) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    // Consulta preparada que actualiza solo registros permitidos para el usuario.
    $consulta = $conexion->prepare(
        'UPDATE subtareas s
         INNER JOIN tareas t ON t.id = s.id_tarea
         SET s.id_tarea = :id_tarea,
             s.titulo = :titulo,
             s.descripcion = :descripcion,
             s.completada = :completada
         WHERE s.id = :id AND t.id_usuario = :id_usuario'
    );
    $consulta->execute([
        ':id_tarea' => $idTarea,
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':completada' => $completada ? 1 : 0,
        ':id' => $idSubtarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    // Registra la actualizacion para la vista de auditoria.
    registrar_log($conexion, (int) $usuario['id'], 'actualizacion', 'subtareas', $idSubtarea, 'Subtarea actualizada: ' . $titulo . ' | Tarea relacionada: ' . $idTarea);
    // Devuelve una respuesta JSON al frontend.
    respuesta_exitosa(['mensaje' => 'Subtarea actualizada correctamente.']);
} catch (Throwable $excepcion) {
    // Oculta detalles internos del error y responde de forma controlada.
    respuesta_error('No se pudo actualizar la subtarea.', 500);
}
