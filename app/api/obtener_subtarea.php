<?php
declare(strict_types=1);

/**
 * Archivo: obtener_subtarea.php
 *
 * Proposito:
 * Endpoint encargado de obtener una subtarea especifica del usuario autenticado.
 *
 * Uso:
 * Se llama desde el frontend cuando se necesita cargar datos para ver o editar
 * una subtarea.
 *
 * Metodo esperado:
 * - GET
 *
 * Entrada esperada:
 * - id {int}: ID de la subtarea solicitada.
 *
 * Salida:
 * - JSON con la subtarea formateada.
 * - JSON de error si la subtarea no existe o no pertenece al usuario.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige GET y usuario autenticado.
 * 3. Valida el ID recibido por query string.
 * 4. Consulta la subtarea junto a su tarea padre.
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

// ID recibido por query string para buscar una subtarea concreta.
$idSubtarea = normalizar_entero_positivo($_GET['id'] ?? null, 'El ID de la subtarea');

/* ============================================================
   Consulta de la subtarea
   ============================================================ */

try {
    // Busca la subtarea y verifica propiedad usando la tarea padre.
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
         WHERE s.id = :id AND t.id_usuario = :id_usuario
         LIMIT 1'
    );
    $consulta->execute([
        ':id' => $idSubtarea,
        ':id_usuario' => (int) $usuario['id'],
    ]);

    // Fila encontrada en base de datos, o false si no existe.
    $subtarea = $consulta->fetch();

    // Si no hay resultado, se evita mostrar informacion de otros usuarios.
    if (!$subtarea) {
        respuesta_error('La subtarea no existe o no pertenece a tu cuenta.', 404);
    }

    // Registra la lectura de la subtarea.
    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'subtareas', $idSubtarea, 'Consulta de subtarea: ' . (string) $subtarea['titulo']);
    // Devuelve la subtarea formateada.
    respuesta_exitosa(['subtarea' => formatear_subtarea($subtarea)]);
} catch (Throwable $excepcion) {
    // Respuesta generica si falla la consulta.
    respuesta_error('No se pudo cargar la subtarea.', 500);
}
