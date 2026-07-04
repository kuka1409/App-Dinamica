<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');

$usuario = usuario_autenticado();
$idUsuario = $usuario !== null ? (int) $usuario['id'] : null;

if ($idUsuario !== null) {
    registrar_log($conexion, $idUsuario, 'cierre_sesion', 'Cierre sesión', 'usuarios', $idUsuario, 'Usuario cerró sesión');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], (bool) $parametros['secure'], (bool) $parametros['httponly']);
}

session_destroy();

respuesta_exitosa(['mensaje' => 'Sesión cerrada correctamente.']);
