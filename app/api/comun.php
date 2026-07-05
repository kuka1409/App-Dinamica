<?php
declare(strict_types=1);

/**
 * Archivo: comun.php
 *
 * Proposito:
 * Contiene configuracion y funciones reutilizables para todos los endpoints
 * de la carpeta api.
 *
 * Uso:
 * Cada archivo PHP de la API lo carga con require_once para tener acceso a:
 * respuestas JSON, validaciones, sesion, auditoria, formateadores y helpers.
 *
 * Responsabilidades:
 * - Configurar salida JSON.
 * - Cargar conexion y funciones generales.
 * - Iniciar sesion segura.
 * - Validar metodos HTTP.
 * - Leer JSON de entrada.
 * - Normalizar datos recibidos.
 * - Validar usuario autenticado y rol auditor.
 * - Registrar movimientos en logs_sistema.
 * - Formatear tareas y subtareas para el frontend.
 */

/* ============================================================
   Configuracion general de la API
   ============================================================ */

// Evita mostrar errores tecnicos directamente al navegador.
ini_set('display_errors', '0');
// Mantiene el reporte interno de errores activo.
error_reporting(E_ALL);
// Todos los endpoints que cargan este archivo responden en formato JSON.
header('Content-Type: application/json; charset=utf-8');

/* ============================================================
   Carga de dependencias compartidas
   ============================================================ */

// Conexion PDO a la base de datos.
require_once __DIR__ . '/../conexion.php';
// Funciones generales, incluyendo manejo de sesion.
require_once __DIR__ . '/../includes/funciones.php';

// Inicia o recupera la sesion de forma centralizada.
iniciar_sesion_segura();

/**
 * Proposito:
 * Envia una respuesta JSON al navegador y termina la ejecucion del endpoint.
 *
 * Uso:
 * Es la funcion base usada por respuesta_exitosa y respuesta_error.
 *
 * Argumentos:
 * - $contenido {array}: Datos que se convertiran a JSON.
 * - $codigoEstado {int}: Codigo HTTP que tendra la respuesta.
 *
 * Retorna:
 * - {never}: No retorna al flujo normal, porque finaliza con exit.
 *
 * Flujo:
 * 1. Define el codigo HTTP.
 * 2. Convierte el arreglo a JSON.
 * 3. Imprime la respuesta.
 * 4. Finaliza el script.
 */
function respuesta_json(array $contenido, int $codigoEstado = 200): never
{
    http_response_code($codigoEstado);
    echo json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Proposito:
 * Construye una respuesta JSON positiva con exito true.
 *
 * Uso:
 * Se usa cuando una operacion de la API termina correctamente.
 *
 * Argumentos:
 * - $datos {array}: Datos adicionales que se enviaran al frontend.
 * - $codigoEstado {int}: Codigo HTTP de la respuesta exitosa.
 *
 * Retorna:
 * - {never}: Envia JSON y finaliza la ejecucion.
 *
 * Flujo:
 * 1. Agrega exito true a los datos recibidos.
 * 2. Delega el envio a respuesta_json.
 */
function respuesta_exitosa(array $datos = [], int $codigoEstado = 200): never
{
    respuesta_json(array_merge(['exito' => true], $datos), $codigoEstado);
}

/**
 * Proposito:
 * Construye una respuesta JSON de error con exito false.
 *
 * Uso:
 * Se usa cuando hay validaciones fallidas, falta sesion, permisos insuficientes
 * o errores controlados.
 *
 * Argumentos:
 * - $mensaje {string}: Mensaje que recibira el frontend.
 * - $codigoEstado {int}: Codigo HTTP del error.
 *
 * Retorna:
 * - {never}: Envia JSON y finaliza la ejecucion.
 *
 * Flujo:
 * 1. Crea un arreglo con exito false y mensaje.
 * 2. Delega el envio a respuesta_json.
 */
function respuesta_error(string $mensaje, int $codigoEstado = 400): never
{
    respuesta_json(['exito' => false, 'mensaje' => $mensaje], $codigoEstado);
}

/**
 * Proposito:
 * Verifica que la peticion use el metodo HTTP esperado.
 *
 * Uso:
 * Cada endpoint lo llama al inicio para aceptar solo GET o POST segun corresponda.
 *
 * Argumentos:
 * - $metodo {string}: Metodo HTTP permitido para el endpoint.
 *
 * Retorna:
 * - {void}: No retorna datos. Si el metodo es incorrecto, responde error.
 *
 * Flujo:
 * 1. Lee REQUEST_METHOD desde el servidor.
 * 2. Compara contra el metodo esperado.
 * 3. Si no coincide, responde con error 405.
 */
function requerir_metodo(string $metodo): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== strtoupper($metodo)) {
        respuesta_error('Método no permitido.', 405);
    }
}

/**
 * Proposito:
 * Lee el cuerpo JSON enviado por el frontend.
 *
 * Uso:
 * Se usa en endpoints POST que reciben datos desde fetch.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {array}: Datos decodificados desde JSON, o arreglo vacio si no hubo cuerpo.
 *
 * Flujo:
 * 1. Lee php://input.
 * 2. Si esta vacio, retorna arreglo vacio.
 * 3. Decodifica JSON a arreglo asociativo.
 * 4. Si el JSON es invalido, responde error.
 * 5. Retorna los datos.
 */
function obtener_entrada_json(): array
{
    // Cuerpo original de la peticion HTTP.
    $entradaCruda = file_get_contents('php://input');

    if ($entradaCruda === false || trim($entradaCruda) === '') {
        return [];
    }

    // Datos convertidos desde JSON a arreglo asociativo.
    $datos = json_decode($entradaCruda, true);

    if (!is_array($datos)) {
        respuesta_error('JSON inválido.', 400);
    }

    return $datos;
}

/**
 * Proposito:
 * Convierte un valor simple a texto limpio.
 *
 * Uso:
 * Se usa antes de validar campos recibidos desde JSON o query string.
 *
 * Argumentos:
 * - $valor {mixed}: Valor original recibido desde el frontend.
 *
 * Retorna:
 * - {string}: Texto sin espacios extremos, o cadena vacia si el valor no es simple.
 *
 * Flujo:
 * 1. Descarta arreglos y objetos.
 * 2. Convierte el valor a string.
 * 3. Limpia espacios al inicio y al final.
 */
function valor_a_texto(mixed $valor): string
{
    if (is_array($valor) || is_object($valor)) {
        return '';
    }

    return trim((string) $valor);
}

/**
 * Proposito:
 * Calcula el largo de un texto de forma compatible con caracteres multibyte.
 *
 * Uso:
 * Se usa para validar largos maximos en campos de texto.
 *
 * Argumentos:
 * - $texto {string}: Texto que se quiere medir.
 *
 * Retorna:
 * - {int}: Cantidad de caracteres del texto.
 *
 * Flujo:
 * 1. Usa mb_strlen si esta disponible.
 * 2. Si no existe, usa strlen como respaldo.
 */
function longitud_texto(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto) : strlen($texto);
}

/**
 * Proposito:
 * Limpia y valida un texto obligatorio.
 *
 * Uso:
 * Se usa para campos como nombre, titulo o correo.
 *
 * Argumentos:
 * - $valor {mixed}: Valor recibido desde el frontend.
 * - $longitudMaxima {int}: Maximo de caracteres permitido.
 * - $etiquetaCampo {string}: Nombre del campo para mensajes de error.
 *
 * Retorna:
 * - {string}: Texto validado.
 *
 * Flujo:
 * 1. Convierte el valor a texto limpio.
 * 2. Verifica que no este vacio.
 * 3. Verifica que no supere el largo maximo.
 * 4. Retorna el texto.
 */
function normalizar_texto_obligatorio(mixed $valor, int $longitudMaxima, string $etiquetaCampo): string
{
    // Texto limpio que sera validado.
    $texto = valor_a_texto($valor);

    if ($texto === '') {
        respuesta_error($etiquetaCampo . ' es obligatorio.', 422);
    }

    if (longitud_texto($texto) > $longitudMaxima) {
        respuesta_error($etiquetaCampo . ' debe tener máximo ' . $longitudMaxima . ' caracteres.', 422);
    }

    return $texto;
}

/**
 * Proposito:
 * Limpia y valida un texto opcional.
 *
 * Uso:
 * Se usa para campos como descripcion.
 *
 * Argumentos:
 * - $valor {mixed}: Valor recibido desde el frontend.
 * - $longitudMaxima {int}: Maximo de caracteres permitido.
 * - $etiquetaCampo {string}: Nombre del campo para mensajes de error.
 *
 * Retorna:
 * - {?string}: Texto validado o null si venia vacio.
 *
 * Flujo:
 * 1. Convierte el valor a texto limpio.
 * 2. Si queda vacio, retorna null.
 * 3. Verifica largo maximo.
 * 4. Retorna el texto.
 */
function normalizar_texto_opcional(mixed $valor, int $longitudMaxima, string $etiquetaCampo): ?string
{
    $texto = valor_a_texto($valor);

    if ($texto === '') {
        return null;
    }

    if (longitud_texto($texto) > $longitudMaxima) {
        respuesta_error($etiquetaCampo . ' debe tener máximo ' . $longitudMaxima . ' caracteres.', 422);
    }

    return $texto;
}

/**
 * Proposito:
 * Valida que un valor sea un entero positivo.
 *
 * Uso:
 * Se usa para IDs recibidos desde JSON o query string.
 *
 * Argumentos:
 * - $valor {mixed}: Valor recibido desde el frontend.
 * - $etiquetaCampo {string}: Nombre del campo para mensajes de error.
 *
 * Retorna:
 * - {int}: Numero entero mayor o igual a 1.
 *
 * Flujo:
 * 1. Intenta validar el valor como entero.
 * 2. Rechaza valores no numericos o menores a 1.
 * 3. Retorna el numero validado.
 */
function normalizar_entero_positivo(mixed $valor, string $etiquetaCampo): int
{
    // Resultado de validar el valor como entero.
    $numero = filter_var($valor, FILTER_VALIDATE_INT);

    if ($numero === false || $numero < 1) {
        respuesta_error($etiquetaCampo . ' es inválido.', 422);
    }

    return $numero;
}

/**
 * Proposito:
 * Convierte valores comunes a booleano.
 *
 * Uso:
 * Se usa para campos como completada.
 *
 * Argumentos:
 * - $valor {mixed}: Valor recibido desde el frontend.
 * - $etiquetaCampo {string}: Nombre del campo para mensajes de error.
 *
 * Retorna:
 * - {bool}: true o false segun el valor recibido.
 *
 * Flujo:
 * 1. Si ya es booleano, lo retorna.
 * 2. Acepta 1 y "1" como true.
 * 3. Acepta 0 y "0" como false.
 * 4. Acepta "true" y "false" como texto.
 * 5. Si no reconoce el valor, responde error.
 */
function normalizar_booleano(mixed $valor, string $etiquetaCampo): bool
{
    if (is_bool($valor)) {
        return $valor;
    }

    if ($valor === 1 || $valor === '1') {
        return true;
    }

    if ($valor === 0 || $valor === '0') {
        return false;
    }

    if (is_string($valor)) {
        // Version en minusculas para aceptar true/false escritos como texto.
        $normalizado = strtolower(trim($valor));

        if ($normalizado === 'true') {
            return true;
        }

        if ($normalizado === 'false') {
            return false;
        }
    }

    respuesta_error($etiquetaCampo . ' es inválido.', 422);
}

/**
 * Proposito:
 * Obtiene el usuario autenticado para endpoints protegidos.
 *
 * Uso:
 * Se usa en endpoints que requieren sesion activa.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {array}: Datos del usuario guardado en sesion.
 *
 * Flujo:
 * 1. Lee el usuario autenticado.
 * 2. Si no existe, responde error 401.
 * 3. Si existe, retorna sus datos.
 */
function usuario_api(): array
{
    // Usuario guardado actualmente en la sesion.
    $usuario = usuario_autenticado();

    if ($usuario === null) {
        respuesta_error('Debes iniciar sesión para realizar esta acción.', 401);
    }

    return $usuario;
}

/**
 * Proposito:
 * Obtiene el usuario autenticado y exige rol auditor.
 *
 * Uso:
 * Se usa en endpoints exclusivos de auditoria.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {array}: Datos del usuario auditor.
 *
 * Flujo:
 * 1. Obtiene el usuario con usuario_api.
 * 2. Revisa su rol.
 * 3. Si no es auditor, responde error 403.
 * 4. Si es auditor, retorna sus datos.
 */
function requerir_auditor_api(): array
{
    $usuario = usuario_api();

    if (($usuario['rol'] ?? '') !== 'auditor') {
        respuesta_error('Acceso permitido solo para auditores.', 403);
    }

    return $usuario;
}

/**
 * Proposito:
 * Intenta obtener la IP del cliente desde cabeceras HTTP.
 *
 * Uso:
 * Se usa al registrar logs del sistema.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {?string}: IP detectada o null si no se encuentra.
 *
 * Flujo:
 * 1. Recorre cabeceras posibles de IP.
 * 2. Ignora valores vacios o invalidos.
 * 3. Si hay varias IP separadas por coma, toma la primera.
 * 4. Retorna la IP encontrada.
 */
function obtener_ip_cliente(): ?string
{
    // Cabeceras posibles donde puede venir la IP del cliente.
    $cabeceras = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];

    foreach ($cabeceras as $cabecera) {
        // Valor encontrado para la cabecera actual.
        $valor = $_SERVER[$cabecera] ?? '';

        if (!is_string($valor) || trim($valor) === '') {
            continue;
        }

        // Algunas cabeceras pueden contener varias IP separadas por coma.
        $partes = explode(',', $valor);
        $ip = trim($partes[0]);

        if ($ip !== '') {
            return $ip;
        }
    }

    return null;
}

/**
 * Proposito:
 * Detecta el sistema operativo usando el user agent.
 *
 * Uso:
 * Se usa al registrar logs para mostrar informacion del dispositivo.
 *
 * Argumentos:
 * - $userAgent {string}: Cadena enviada por el navegador.
 *
 * Retorna:
 * - {string}: Nombre del sistema operativo detectado o No identificado.
 *
 * Flujo:
 * 1. Define patrones de busqueda.
 * 2. Recorre cada patron.
 * 3. Retorna el primer sistema que coincida.
 * 4. Si no hay coincidencia, retorna No identificado.
 */
function detectar_sistema_operativo(string $userAgent): string
{
    // Patrones usados para reconocer sistemas conocidos.
    $patrones = [
        'Windows' => '/windows nt|win64|win32/i',
        'macOS' => '/macintosh|mac os x/i',
        'Android' => '/android/i',
        'iOS' => '/iphone|ipad|ipod/i',
        'Linux' => '/linux/i',
    ];

    foreach ($patrones as $nombre => $patron) {
        if (preg_match($patron, $userAgent)) {
            return $nombre;
        }
    }

    return 'No identificado';
}

/**
 * Proposito:
 * Detecta el navegador usando el user agent.
 *
 * Uso:
 * Se usa al registrar logs para mostrar informacion del navegador.
 *
 * Argumentos:
 * - $userAgent {string}: Cadena enviada por el navegador.
 *
 * Retorna:
 * - {string}: Nombre del navegador detectado o No identificado.
 *
 * Flujo:
 * 1. Define patrones de navegadores conocidos.
 * 2. Recorre cada patron.
 * 3. Retorna el primer navegador que coincida.
 * 4. Si no hay coincidencia, retorna No identificado.
 */
function detectar_navegador(string $userAgent): string
{
    // Patrones usados para reconocer navegadores conocidos.
    $patrones = [
        'Microsoft Edge' => '/edg\//i',
        'Opera' => '/opr\//i',
        'Chrome' => '/chrome\//i',
        'Firefox' => '/firefox\//i',
        'Safari' => '/safari\//i',
    ];

    foreach ($patrones as $nombre => $patron) {
        if (preg_match($patron, $userAgent)) {
            return $nombre;
        }
    }

    return 'No identificado';
}


/**
 * Proposito:
 * Obtiene la zona horaria oficial usada para los registros de auditoria.
 *
 * Uso:
 * Se usa para que la fecha y hora del log no dependan del reloj interno
 * del contenedor ni de la configuracion por defecto de MySQL.
 *
 * Retorna:
 * - {DateTimeZone}: Zona horaria configurada para la aplicacion.
 *
 * Flujo:
 * 1. Lee APP_TIMEZONE si esta definida.
 * 2. Usa America/Santiago como valor por defecto.
 * 3. Si la zona es invalida, vuelve a America/Santiago.
 */
function obtener_zona_horaria_auditoria(): DateTimeZone
{
    $zonaHorariaApp = defined('APP_TIMEZONE')
        ? (string) APP_TIMEZONE
        : (getenv('APP_TIMEZONE') ?: 'America/Santiago');

    try {
        return new DateTimeZone($zonaHorariaApp);
    } catch (Throwable $excepcion) {
        return new DateTimeZone('America/Santiago');
    }
}

/**
 * Proposito:
 * Genera la fecha y hora exacta para guardar un evento de auditoria.
 *
 * Uso:
 * Se usa al insertar logs para forzar la hora local de Chile desde PHP.
 *
 * Retorna:
 * - {string}: Fecha y hora en formato YYYY-MM-DD HH:MM:SS.
 */
function obtener_fecha_hora_auditoria(): string
{
    return (new DateTimeImmutable('now', obtener_zona_horaria_auditoria()))->format('Y-m-d H:i:s');
}

/**
 * Proposito:
 * Obtiene datos de usuario para guardarlos dentro de un log.
 *
 * Uso:
 * Se usa desde registrar_log para guardar nombre, correo y rol historicos.
 *
 * Argumentos:
 * - $conexion {PDO}: Conexion activa a la base de datos.
 * - $idUsuario {?int}: ID del usuario relacionado con el evento.
 *
 * Retorna:
 * - {array}: Nombre, correo y rol del usuario, o valores null si no existe.
 *
 * Flujo:
 * 1. Intenta usar primero el usuario guardado en sesion.
 * 2. Si no hay ID, retorna datos null.
 * 3. Si hay ID, consulta la tabla usuarios.
 * 4. Retorna datos del usuario o null si no existe.
 */
function obtener_usuario_log(PDO $conexion, ?int $idUsuario): array
{
    // Usuario actualmente guardado en sesion.
    $usuarioSesion = usuario_autenticado();

    if ($usuarioSesion !== null && ($idUsuario === null || (int) $usuarioSesion['id'] === $idUsuario)) {
        return [
            'nombre' => (string) ($usuarioSesion['nombre'] ?? ''),
            'correo' => (string) ($usuarioSesion['correo'] ?? ''),
            'rol' => (string) ($usuarioSesion['rol'] ?? ''),
        ];
    }

    if ($idUsuario === null) {
        return ['nombre' => null, 'correo' => null, 'rol' => null];
    }

    // Si el usuario no esta en sesion, se consulta la base de datos.
    $consulta = $conexion->prepare('SELECT nombre, correo, rol FROM usuarios WHERE id = :id LIMIT 1');
    $consulta->execute([':id' => $idUsuario]);
    $usuario = $consulta->fetch();

    if (!$usuario) {
        return ['nombre' => null, 'correo' => null, 'rol' => null];
    }

    return [
        'nombre' => (string) $usuario['nombre'],
        'correo' => (string) $usuario['correo'],
        'rol' => (string) $usuario['rol'],
    ];
}

/**
 * Proposito:
 * Inserta un registro en la tabla logs_sistema.
 *
 * Uso:
 * Se usa en endpoints de creacion, lectura, actualizacion, eliminacion y sesion.
 *
 * Argumentos:
 * - $conexion {PDO}: Conexion activa a la base de datos.
 * - $idUsuario {?int}: ID del usuario que realiza o provoca la accion.
 * - $tipoMovimiento {string}: Tipo de accion registrada.
 * - $tablaAfectada {?string}: Tabla relacionada con el evento.
 * - $idRegistro {mixed}: ID del registro afectado.
 * - $detalle {?string}: Descripcion humana del evento.
 *
 * Retorna:
 * - {void}: No retorna datos. Inserta el log si es posible.
 *
 * Flujo:
 * 1. Obtiene user agent, IP, sistema operativo y navegador.
 * 2. Obtiene datos historicos del usuario.
 * 3. Prepara un INSERT en logs_sistema.
 * 4. Ejecuta el INSERT.
 * 5. Si falla el log, no rompe la accion principal.
 */
function registrar_log(
    PDO $conexion,
    ?int $idUsuario,
    string $tipoMovimiento,
    ?string $tablaAfectada = null,
    mixed $idRegistro = null,
    ?string $detalle = null
): void {
    try {
        // User agent original enviado por el navegador.
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userAgent = is_string($userAgent) ? $userAgent : '';
        // Datos del usuario que quedaran congelados dentro del log.
        $usuarioLog = obtener_usuario_log($conexion, $idUsuario);

        // Consulta preparada para insertar el registro de auditoria.
        $consulta = $conexion->prepare(
            'INSERT INTO logs_sistema (
                id_usuario,
                usuario_nombre,
                usuario_correo,
                usuario_rol,
                tipo_movimiento,
                tabla_afectada,
                id_registro,
                detalle,
                ip,
                sistema_operativo,
                navegador,
                user_agent,
                fecha_evento
             ) VALUES (
                :id_usuario,
                :usuario_nombre,
                :usuario_correo,
                :usuario_rol,
                :tipo_movimiento,
                :tabla_afectada,
                :id_registro,
                :detalle,
                :ip,
                :sistema_operativo,
                :navegador,
                :user_agent,
                :fecha_evento
             )'
        );

        $consulta->execute([
            ':id_usuario' => $idUsuario,
            ':usuario_nombre' => $usuarioLog['nombre'],
            ':usuario_correo' => $usuarioLog['correo'],
            ':usuario_rol' => $usuarioLog['rol'],
            ':tipo_movimiento' => $tipoMovimiento,
            ':tabla_afectada' => $tablaAfectada,
            ':id_registro' => $idRegistro !== null ? (string) $idRegistro : null,
            ':detalle' => $detalle,
            ':ip' => obtener_ip_cliente(),
            ':sistema_operativo' => detectar_sistema_operativo($userAgent),
            ':navegador' => detectar_navegador($userAgent),
            ':user_agent' => $userAgent !== '' ? $userAgent : null,
            ':fecha_evento' => obtener_fecha_hora_auditoria(),
        ]);
    } catch (Throwable $excepcion) {
        // El log no debe romper la operacion principal.
    }
}

/**
 * Proposito:
 * Verifica si una tarea pertenece a un usuario.
 *
 * Uso:
 * Se usa antes de obtener, modificar o eliminar tareas.
 *
 * Argumentos:
 * - $conexion {PDO}: Conexion activa a la base de datos.
 * - $idUsuario {int}: ID del usuario autenticado.
 * - $idTarea {int}: ID de la tarea que se quiere validar.
 *
 * Retorna:
 * - {bool}: true si la tarea pertenece al usuario, false en caso contrario.
 *
 * Flujo:
 * 1. Consulta la tarea por ID y usuario.
 * 2. Retorna true si encuentra una fila.
 */
function usuario_posee_tarea(PDO $conexion, int $idUsuario, int $idTarea): bool
{
    $consulta = $conexion->prepare('SELECT id FROM tareas WHERE id = :id AND id_usuario = :id_usuario LIMIT 1');
    $consulta->execute([':id' => $idTarea, ':id_usuario' => $idUsuario]);
    return $consulta->fetchColumn() !== false;
}

/**
 * Proposito:
 * Verifica si una subtarea pertenece a un usuario.
 *
 * Uso:
 * Se usa antes de obtener, modificar o eliminar subtareas.
 *
 * Argumentos:
 * - $conexion {PDO}: Conexion activa a la base de datos.
 * - $idUsuario {int}: ID del usuario autenticado.
 * - $idSubtarea {int}: ID de la subtarea que se quiere validar.
 *
 * Retorna:
 * - {bool}: true si la subtarea pertenece al usuario, false en caso contrario.
 *
 * Flujo:
 * 1. Une subtareas con tareas.
 * 2. Verifica que la tarea padre pertenezca al usuario.
 * 3. Retorna true si encuentra una fila.
 */
function usuario_posee_subtarea(PDO $conexion, int $idUsuario, int $idSubtarea): bool
{
    $consulta = $conexion->prepare(
        'SELECT s.id
         FROM subtareas s
         INNER JOIN tareas t ON t.id = s.id_tarea
         WHERE s.id = :id AND t.id_usuario = :id_usuario
         LIMIT 1'
    );
    $consulta->execute([':id' => $idSubtarea, ':id_usuario' => $idUsuario]);
    return $consulta->fetchColumn() !== false;
}

/**
 * Proposito:
 * Calcula el estado general de una tarea segun sus subtareas.
 *
 * Uso:
 * Se usa al formatear tareas para el frontend.
 *
 * Argumentos:
 * - $totalSubtareas {int}: Cantidad total de subtareas.
 * - $subtareasCompletadas {int}: Cantidad de subtareas completadas.
 *
 * Retorna:
 * - {string}: vacio, completado o en_progreso.
 *
 * Flujo:
 * 1. Si no hay subtareas, retorna vacio.
 * 2. Si todas estan completas, retorna completado.
 * 3. En cualquier otro caso, retorna en_progreso.
 */
function calcular_estado_tarea(int $totalSubtareas, int $subtareasCompletadas): string
{
    if ($totalSubtareas <= 0) {
        return 'vacio';
    }

    if ($subtareasCompletadas >= $totalSubtareas) {
        return 'completado';
    }

    return 'en_progreso';
}

/**
 * Proposito:
 * Convierte una fila SQL de tareas en un arreglo limpio para el frontend.
 *
 * Uso:
 * Se usa en endpoints que listan u obtienen tareas.
 *
 * Argumentos:
 * - $fila {array}: Fila recibida desde PDO.
 *
 * Retorna:
 * - {array}: Tarea normalizada con resumen de subtareas.
 *
 * Flujo:
 * 1. Convierte conteos a enteros.
 * 2. Calcula estado general de la tarea.
 * 3. Normaliza tipos de datos.
 * 4. Retorna el arreglo final.
 */
function formatear_tarea(array $fila): array
{
    // Total de subtareas asociadas a la tarea.
    $totalSubtareas = (int) ($fila['total_subtareas'] ?? 0);
    // Cantidad de subtareas ya completadas.
    $subtareasCompletadas = (int) ($fila['subtareas_completadas'] ?? 0);

    return [
        'id' => (int) $fila['id'],
        'titulo' => (string) $fila['titulo'],
        'descripcion' => $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
        'estado' => calcular_estado_tarea($totalSubtareas, $subtareasCompletadas),
        'total_subtareas' => $totalSubtareas,
        'subtareas_completadas' => $subtareasCompletadas,
        'subtareas_pendientes' => max(0, $totalSubtareas - $subtareasCompletadas),
        'fecha_creacion' => (string) $fila['fecha_creacion'],
        'fecha_actualizacion' => (string) $fila['fecha_actualizacion'],
    ];
}

/**
 * Proposito:
 * Convierte una fila SQL de subtareas en un arreglo limpio para el frontend.
 *
 * Uso:
 * Se usa en endpoints que listan u obtienen subtareas.
 *
 * Argumentos:
 * - $fila {array}: Fila recibida desde PDO.
 *
 * Retorna:
 * - {array}: Subtarea normalizada.
 *
 * Flujo:
 * 1. Convierte IDs y posicion a enteros.
 * 2. Convierte completada a booleano.
 * 3. Normaliza textos y fechas.
 * 4. Retorna el arreglo final.
 */
function formatear_subtarea(array $fila): array
{
    return [
        'id' => (int) $fila['id'],
        'id_tarea' => (int) $fila['id_tarea'],
        'titulo_tarea' => isset($fila['titulo_tarea']) ? (string) $fila['titulo_tarea'] : null,
        'titulo' => (string) $fila['titulo'],
        'descripcion' => $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
        'completada' => ((int) $fila['completada']) === 1,
        'posicion' => (int) $fila['posicion'],
        'fecha_creacion' => (string) $fila['fecha_creacion'],
        'fecha_actualizacion' => (string) $fila['fecha_actualizacion'],
    ];
}
