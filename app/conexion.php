<?php
declare(strict_types=1);

$zonaHorariaApp = getenv('APP_TIMEZONE') ?: 'America/Santiago';

if (!date_default_timezone_set($zonaHorariaApp)) {
    $zonaHorariaApp = 'America/Santiago';
    date_default_timezone_set($zonaHorariaApp);
}

if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', $zonaHorariaApp);
}

$servidor = getenv('DB_HOST') ?: 'base_datos';
$puerto = getenv('DB_PORT') ?: '3306';
$nombreBaseDatos = getenv('DB_NAME') ?: 'aplicacion_dinamica';
$usuario = getenv('DB_USER') ?: 'usuario_app';
$contrasena = getenv('DB_PASSWORD') ?: 'clave_app';

try {
    $cadenaConexion = "mysql:host={$servidor};port={$puerto};dbname={$nombreBaseDatos};charset=utf8mb4";

    $conexion = new PDO(
        $cadenaConexion,
        $usuario,
        $contrasena,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // MySQL suele trabajar en UTC dentro de Docker. Esta línea alinea la sesión
    // de la base de datos con la zona horaria configurada para la aplicación.
    $zonaHoraria = new DateTimeZone($zonaHorariaApp);
    $offsetZonaHoraria = (new DateTimeImmutable('now', $zonaHoraria))->format('P');
    $conexion->exec('SET time_zone = ' . $conexion->quote($offsetZonaHoraria));
} catch (PDOException $excepcion) {
    $esPeticionApi = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/');

    if ($esPeticionApi) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        http_response_code(500);
        echo json_encode(
            [
                'exito' => false,
                'mensaje' => 'Error de conexión con la base de datos.',
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    http_response_code(500);
    exit('Error de conexión con la base de datos.');
}
