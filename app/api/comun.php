<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

iniciar_sesion_segura();

function respuesta_json(array $contenido, int $codigoEstado = 200): never
{
    http_response_code($codigoEstado);
    echo json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function respuesta_exitosa(array $datos = [], int $codigoEstado = 200): never
{
    respuesta_json(array_merge(['exito' => true], $datos), $codigoEstado);
}

function respuesta_error(string $mensaje, int $codigoEstado = 400): never
{
    respuesta_json(['exito' => false, 'mensaje' => $mensaje], $codigoEstado);
}

function requerir_metodo(string $metodo): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== strtoupper($metodo)) {
        respuesta_error('Método no permitido.', 405);
    }
}

function obtener_entrada_json(): array
{
    $entradaCruda = file_get_contents('php://input');

    if ($entradaCruda === false || trim($entradaCruda) === '') {
        return [];
    }

    $datos = json_decode($entradaCruda, true);

    if (!is_array($datos)) {
        respuesta_error('JSON inválido.', 400);
    }

    return $datos;
}

function valor_a_texto(mixed $valor): string
{
    if (is_array($valor) || is_object($valor)) {
        return '';
    }

    return trim((string) $valor);
}

function longitud_texto(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto) : strlen($texto);
}

function normalizar_texto_obligatorio(mixed $valor, int $longitudMaxima, string $etiquetaCampo): string
{
    $texto = valor_a_texto($valor);

    if ($texto === '') {
        respuesta_error($etiquetaCampo . ' es obligatorio.', 422);
    }

    if (longitud_texto($texto) > $longitudMaxima) {
        respuesta_error($etiquetaCampo . ' debe tener máximo ' . $longitudMaxima . ' caracteres.', 422);
    }

    return $texto;
}

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

function normalizar_entero_positivo(mixed $valor, string $etiquetaCampo): int
{
    $numero = filter_var($valor, FILTER_VALIDATE_INT);

    if ($numero === false || $numero < 1) {
        respuesta_error($etiquetaCampo . ' es inválido.', 422);
    }

    return $numero;
}

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

function usuario_api(): array
{
    $usuario = usuario_autenticado();

    if ($usuario === null) {
        respuesta_error('Debes iniciar sesión para realizar esta acción.', 401);
    }

    return $usuario;
}

function requerir_auditor_api(): array
{
    $usuario = usuario_api();

    if (($usuario['rol'] ?? '') !== 'auditor') {
        respuesta_error('Acceso permitido solo para auditores.', 403);
    }

    return $usuario;
}

function obtener_ip_cliente(): ?string
{
    $cabeceras = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];

    foreach ($cabeceras as $cabecera) {
        $valor = $_SERVER[$cabecera] ?? '';

        if (!is_string($valor) || trim($valor) === '') {
            continue;
        }

        $partes = explode(',', $valor);
        $ip = trim($partes[0]);

        if ($ip !== '') {
            return $ip;
        }
    }

    return null;
}

function detectar_sistema_operativo(string $userAgent): string
{
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

function detectar_navegador(string $userAgent): string
{
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

function obtener_usuario_log(PDO $conexion, ?int $idUsuario): array
{
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

function registrar_log(
    PDO $conexion,
    ?int $idUsuario,
    string $tipoMovimiento,
    string $ubicacion,
    ?string $tablaAfectada = null,
    mixed $idRegistro = null,
    ?string $detalle = null
): void {
    try {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userAgent = is_string($userAgent) ? $userAgent : '';
        $usuarioLog = obtener_usuario_log($conexion, $idUsuario);

        $consulta = $conexion->prepare(
            'INSERT INTO logs_sistema (
                id_usuario,
                usuario_nombre,
                usuario_correo,
                usuario_rol,
                tipo_movimiento,
                ubicacion,
                tabla_afectada,
                id_registro,
                detalle,
                ip,
                sistema_operativo,
                navegador,
                user_agent
             ) VALUES (
                :id_usuario,
                :usuario_nombre,
                :usuario_correo,
                :usuario_rol,
                :tipo_movimiento,
                :ubicacion,
                :tabla_afectada,
                :id_registro,
                :detalle,
                :ip,
                :sistema_operativo,
                :navegador,
                :user_agent
             )'
        );

        $consulta->execute([
            ':id_usuario' => $idUsuario,
            ':usuario_nombre' => $usuarioLog['nombre'],
            ':usuario_correo' => $usuarioLog['correo'],
            ':usuario_rol' => $usuarioLog['rol'],
            ':tipo_movimiento' => $tipoMovimiento,
            ':ubicacion' => $ubicacion,
            ':tabla_afectada' => $tablaAfectada,
            ':id_registro' => $idRegistro !== null ? (string) $idRegistro : null,
            ':detalle' => $detalle,
            ':ip' => obtener_ip_cliente(),
            ':sistema_operativo' => detectar_sistema_operativo($userAgent),
            ':navegador' => detectar_navegador($userAgent),
            ':user_agent' => $userAgent !== '' ? $userAgent : null,
        ]);
    } catch (Throwable $excepcion) {
        // El log no debe romper la operación principal.
    }
}

function usuario_posee_tarea(PDO $conexion, int $idUsuario, int $idTarea): bool
{
    $consulta = $conexion->prepare('SELECT id FROM tareas WHERE id = :id AND id_usuario = :id_usuario LIMIT 1');
    $consulta->execute([':id' => $idTarea, ':id_usuario' => $idUsuario]);
    return $consulta->fetchColumn() !== false;
}

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

function formatear_tarea(array $fila): array
{
    $totalSubtareas = (int) ($fila['total_subtareas'] ?? 0);
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
