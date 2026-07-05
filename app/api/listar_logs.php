<?php
declare(strict_types=1);

/**
 * Archivo: listar_logs.php
 *
 * Proposito:
 * Endpoint encargado de listar registros de auditoria del sistema.
 *
 * Uso:
 * Se llama desde la vista de auditoria cuando un usuario con rol auditor
 * consulta la tabla de logs o aplica filtros.
 *
 * Metodo esperado:
 * - GET
 *
 * Entrada esperada:
 * - fecha_desde {string}: Fecha inicial opcional en formato YYYY-MM-DD.
 * - fecha_hasta {string}: Fecha final opcional en formato YYYY-MM-DD.
 * - usuario {string}: Texto opcional para buscar por nombre o correo.
 * - tipo_movimiento {string}: Tipo de accion registrada.
 * - modulo {string}: Modulo calculado a partir del movimiento.
 * - tabla_afectada {string}: Tabla relacionada con el evento.
 *
 * Salida:
 * - JSON con una lista de logs filtrados.
 * - JSON de error si el usuario no es auditor o si un filtro es invalido.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige GET y rol auditor.
 * 3. Lee y valida filtros.
 * 4. Construye condiciones SQL dinamicas de forma segura.
 * 5. Consulta hasta 150 registros de auditoria.
 * 6. Registra que el auditor consulto logs.
 * 7. Devuelve respuesta JSON al frontend.
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
// Usuario autenticado con rol auditor.
$usuario = requerir_auditor_api();

/**
 * Proposito:
 * Obtiene un filtro de texto desde $_GET y lo deja listo para usar.
 *
 * Uso:
 * Se usa para filtros como usuario, tipo_movimiento, modulo y tabla_afectada.
 *
 * Argumentos:
 * - $clave {string}: Nombre del parametro recibido por query string.
 * - $maximo {int}: Largo maximo permitido para el texto.
 *
 * Retorna:
 * - {string}: Texto limpio o cadena vacia si no existe filtro.
 *
 * Flujo:
 * 1. Lee el valor desde $_GET.
 * 2. Descarta arreglos para evitar entradas invalidas.
 * 3. Limpia espacios al inicio y al final.
 * 4. Valida largo maximo.
 * 5. Retorna el texto normalizado.
 */
function obtener_filtro_texto(string $clave, int $maximo): string
{
    $valor = $_GET[$clave] ?? '';

    if (is_array($valor)) {
        return '';
    }

    $texto = trim((string) $valor);

    if ($texto === '') {
        return '';
    }

    if (longitud_texto($texto) > $maximo) {
        respuesta_error('El filtro ' . $clave . ' es demasiado largo.', 422);
    }

    return $texto;
}

/**
 * Proposito:
 * Obtiene y valida un filtro de fecha.
 *
 * Uso:
 * Se usa para fecha_desde y fecha_hasta en la vista de auditoria.
 *
 * Argumentos:
 * - $clave {string}: Nombre del parametro recibido por query string.
 *
 * Retorna:
 * - {string}: Fecha valida en formato YYYY-MM-DD o cadena vacia.
 *
 * Flujo:
 * 1. Obtiene el texto usando obtener_filtro_texto.
 * 2. Si no existe valor, retorna cadena vacia.
 * 3. Valida que la fecha use formato YYYY-MM-DD.
 * 4. Si el formato es invalido, responde con error.
 * 5. Retorna la fecha validada.
 */
function obtener_filtro_fecha(string $clave): string
{
    $fecha = obtener_filtro_texto($clave, 10);

    if ($fecha === '') {
        return '';
    }

    $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    $errores = DateTimeImmutable::getLastErrors();

    if (!$fechaValida || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
        respuesta_error('La fecha ingresada no es válida.', 422);
    }

    return $fecha;
}

/**
 * Proposito:
 * Valida que un filtro pertenezca a una lista cerrada de valores permitidos.
 *
 * Uso:
 * Se usa para evitar que filtros como modulo o tipo_movimiento reciban valores
 * inesperados.
 *
 * Argumentos:
 * - $valor {string}: Valor recibido desde el frontend.
 * - $permitidos {array}: Lista de valores aceptados.
 * - $etiqueta {string}: Nombre legible del filtro para mensajes de error.
 *
 * Retorna:
 * - {string}: El mismo valor si es valido, o cadena vacia si no habia filtro.
 *
 * Flujo:
 * 1. Si el valor esta vacio, retorna cadena vacia.
 * 2. Comprueba si el valor existe en la lista permitida.
 * 3. Si no existe, responde con error.
 * 4. Retorna el valor validado.
 */
function validar_filtro_en_lista(string $valor, array $permitidos, string $etiqueta): string
{
    if ($valor === '') {
        return '';
    }

    if (!in_array($valor, $permitidos, true)) {
        respuesta_error('El filtro ' . $etiqueta . ' es inválido.', 422);
    }

    return $valor;
}

/* ============================================================
   Lectura y validacion de filtros
   ============================================================ */

// Fecha inicial para filtrar logs.
$fechaDesde = obtener_filtro_fecha('fecha_desde');
// Fecha final para filtrar logs.
$fechaHasta = obtener_filtro_fecha('fecha_hasta');
// Texto para buscar por nombre o correo de usuario.
$usuarioFiltro = obtener_filtro_texto('usuario', 120);
// Tipo de movimiento permitido por la interfaz.
$tipoMovimiento = validar_filtro_en_lista(
    obtener_filtro_texto('tipo_movimiento', 30),
    ['creacion', 'actualizacion', 'eliminacion', 'lectura', 'inicio_sesion', 'cierre_sesion'],
    'tipo de movimiento'
);
// Modulo visual calculado para clasificar el log.
$modulo = validar_filtro_en_lista(
    obtener_filtro_texto('modulo', 30),
    ['Usuarios', 'Sesión', 'Tareas', 'Subtareas', 'Auditoría'],
    'módulo'
);
// Tabla de base de datos relacionada con el evento.
$tablaAfectada = validar_filtro_en_lista(
    obtener_filtro_texto('tabla_afectada', 40),
    ['usuarios', 'tareas', 'subtareas', 'logs_sistema'],
    'tabla afectada'
);

// Evita rangos de fecha invertidos.
if ($fechaDesde !== '' && $fechaHasta !== '' && $fechaDesde > $fechaHasta) {
    respuesta_error('La fecha desde no puede ser posterior a la fecha hasta.', 422);
}

/* ============================================================
   Construccion dinamica de la consulta
   ============================================================ */

// CASE SQL que transforma datos tecnicos del log en un modulo legible.
$expresionModulo = "CASE
    WHEN l.tipo_movimiento IN ('inicio_sesion', 'cierre_sesion') THEN 'Sesión'
    WHEN l.tabla_afectada = 'usuarios' THEN 'Usuarios'
    WHEN l.tabla_afectada = 'tareas' THEN 'Tareas'
    WHEN l.tabla_afectada = 'subtareas' THEN 'Subtareas'
    WHEN l.tabla_afectada = 'logs_sistema' THEN 'Auditoría'
    ELSE 'Sistema'
END";

// Condiciones WHERE que se agregan solo si hay filtros activos.
$condiciones = [];
// Parametros asociados a la consulta preparada.
$parametros = [];

if ($fechaDesde !== '') {
    $condiciones[] = 'DATE(l.fecha_evento) >= :fecha_desde';
    $parametros[':fecha_desde'] = $fechaDesde;
}

if ($fechaHasta !== '') {
    $condiciones[] = 'DATE(l.fecha_evento) <= :fecha_hasta';
    $parametros[':fecha_hasta'] = $fechaHasta;
}

if ($usuarioFiltro !== '') {
    $condiciones[] = '(l.usuario_nombre LIKE :usuario OR l.usuario_correo LIKE :usuario)';
    $parametros[':usuario'] = '%' . $usuarioFiltro . '%';
}

if ($tipoMovimiento !== '') {
    $condiciones[] = 'l.tipo_movimiento = :tipo_movimiento';
    $parametros[':tipo_movimiento'] = $tipoMovimiento;
}

if ($modulo !== '') {
    $condiciones[] = $expresionModulo . ' = :modulo';
    $parametros[':modulo'] = $modulo;
}

if ($tablaAfectada !== '') {
    $condiciones[] = 'l.tabla_afectada = :tabla_afectada';
    $parametros[':tabla_afectada'] = $tablaAfectada;
}

// Texto final del WHERE. Si no hay filtros, queda vacio.
$where = $condiciones !== [] ? 'WHERE ' . implode(' AND ', $condiciones) : '';

/* ============================================================
   Consulta de logs
   ============================================================ */

try {
    // Consulta preparada que obtiene los ultimos 150 registros segun filtros.
    $consulta = $conexion->prepare(
        'SELECT
            l.id,
            l.id_usuario,
            l.usuario_nombre,
            l.usuario_correo,
            l.usuario_rol,
            l.tipo_movimiento,
            ' . $expresionModulo . ' AS modulo,
            l.tabla_afectada,
            l.id_registro,
            l.detalle,
            l.ip,
            l.sistema_operativo,
            l.navegador,
            l.user_agent,
            l.fecha_evento,
            DATE_FORMAT(l.fecha_evento, "%H:%i:%s") AS hora_evento
         FROM logs_sistema l
         ' . $where . '
         ORDER BY l.fecha_evento DESC, l.id DESC
         LIMIT 150'
    );

    $consulta->execute($parametros);
    // Registros encontrados para mostrar en la tabla de auditoria.
    $logs = $consulta->fetchAll();

    // Lista textual de filtros usados, solo para dejar evidencia en auditoria.
    $filtrosAplicados = [];

    // Construye una descripcion de los filtros realmente aplicados.
    foreach ([
        'fecha_desde' => $fechaDesde,
        'fecha_hasta' => $fechaHasta,
        'usuario' => $usuarioFiltro,
        'tipo_movimiento' => $tipoMovimiento,
        'modulo' => $modulo,
        'tabla_afectada' => $tablaAfectada,
    ] as $clave => $valor) {
        if ($valor !== '') {
            $filtrosAplicados[] = $clave . '=' . $valor;
        }
    }

    // Detalle que se guardara cuando el auditor consulta los logs.
    $detalleLog = 'Auditor consultó los registros del sistema';

    if ($filtrosAplicados !== []) {
        $detalleLog .= ' con filtros: ' . implode(', ', $filtrosAplicados);
    }

    // Registra que un auditor consulto la tabla de logs.
    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'logs_sistema', null, $detalleLog);

    // Devuelve logs filtrados al frontend.
    respuesta_exitosa(['logs' => $logs]);
} catch (Throwable $excepcion) {
    // Respuesta generica si falla la consulta de auditoria.
    respuesta_error('No se pudieron cargar los registros de auditoría.', 500);
}
