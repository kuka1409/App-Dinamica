<?php
declare(strict_types=1);

/**
 * Archivo: cerrar_sesion.php
 *
 * Proposito:
 * Endpoint encargado de cerrar la sesion activa del usuario.
 *
 * Uso:
 * Se llama desde el boton de cerrar sesion del frontend mediante una peticion POST.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - No requiere cuerpo JSON.
 *
 * Salida:
 * - JSON de exito indicando que la sesion fue cerrada.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Obtiene el usuario actual si existe.
 * 4. Registra el cierre de sesion en auditoria.
 * 5. Limpia la informacion de sesion.
 * 6. Elimina la cookie de sesion si corresponde.
 * 7. Destruye la sesion en el servidor.
 * 8. Devuelve respuesta JSON.
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

/* ============================================================
   Lectura del usuario actual
   ============================================================ */

// Usuario de la sesion actual, si existe.
$usuario = usuario_autenticado();
// ID usado para registrar el cierre en auditoria.
$idUsuario = $usuario !== null ? (int) $usuario['id'] : null;

// Solo se registra log si habia una sesion activa.
if ($idUsuario !== null) {
    registrar_log($conexion, $idUsuario, 'cierre_sesion', 'usuarios', $idUsuario, 'Usuario cerró sesión');
}

/* ============================================================
   Limpieza de sesion
   ============================================================ */

// Limpia todos los datos guardados en la sesion.
$_SESSION = [];

// Si PHP usa cookies de sesion, se invalida la cookie en el navegador.
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], (bool) $parametros['secure'], (bool) $parametros['httponly']);
}

// Destruye la sesion almacenada en el servidor.
session_destroy();

/* ============================================================
   Respuesta final
   ============================================================ */

respuesta_exitosa(['mensaje' => 'Sesión cerrada correctamente.']);
