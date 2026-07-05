<?php
declare(strict_types=1);

/**
 * Archivo: iniciar_sesion.php
 *
 * Proposito:
 * Endpoint encargado de validar credenciales e iniciar sesion.
 *
 * Uso:
 * Se llama desde el formulario de login del frontend mediante una peticion POST.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - correo {string}: Correo ingresado por el usuario.
 * - contrasena {string}: Contrasena ingresada por el usuario.
 *
 * Salida:
 * - JSON de exito con los datos basicos del usuario.
 * - JSON de error si las credenciales no son validas.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Lee correo y contrasena desde el JSON.
 * 4. Busca el usuario por correo.
 * 5. Valida la contrasena usando password_verify.
 * 6. Regenera el ID de sesion por seguridad.
 * 7. Guarda datos del usuario en $_SESSION.
 * 8. Registra el inicio de sesion.
 * 9. Devuelve respuesta JSON.
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

// Datos enviados por el frontend en formato JSON.
/* ============================================================
   Lectura y normalizacion de credenciales
   ============================================================ */

// Datos enviados desde el formulario de login.
$datos = obtener_entrada_json();
// Correo normalizado a minusculas para evitar duplicados por mayusculas.
$correo = strtolower(normalizar_texto_obligatorio($datos['correo'] ?? null, 180, 'El correo'));
// Contrasena escrita por el usuario; se compara contra el hash guardado.
$contrasena = valor_a_texto($datos['contrasena'] ?? null);

/* ============================================================
   Busqueda de usuario y validacion de contrasena
   ============================================================ */

try {
    // Busca la cuenta por correo.
    $consulta = $conexion->prepare(
        'SELECT id, nombre, correo, contrasena_hash, rol
         FROM usuarios
         WHERE correo = :correo
         LIMIT 1'
    );
    $consulta->execute([':correo' => $correo]);
    // Registro de usuario encontrado, o false si el correo no existe.
    $usuario = $consulta->fetch();

    // password_verify compara la contrasena ingresada con el hash almacenado.
    if (!$usuario || !password_verify($contrasena, (string) $usuario['contrasena_hash'])) {
        // Registra inicio de sesion correcto.
    registrar_log($conexion, null, 'inicio_sesion', 'usuarios', null, 'Intento fallido de inicio de sesión para correo: ' . $correo);
        respuesta_error('Correo o contraseña incorrectos.', 401);
    }

    // Regenera el ID de sesion para reducir riesgo de fijacion de sesion.
    session_regenerate_id(true);

    // Datos minimos que quedan disponibles para el resto del sistema.
    $_SESSION['usuario'] = [
        'id' => (int) $usuario['id'],
        'nombre' => (string) $usuario['nombre'],
        'correo' => (string) $usuario['correo'],
        'rol' => (string) $usuario['rol'],
    ];

    registrar_log($conexion, (int) $usuario['id'], 'inicio_sesion', 'usuarios', (int) $usuario['id'], 'Usuario inició sesión');

    // Devuelve datos basicos del usuario al frontend.
    respuesta_exitosa([
        'mensaje' => 'Sesión iniciada correctamente.',
        'usuario' => $_SESSION['usuario'],
    ]);
} catch (Throwable $excepcion) {
    // Evita mostrar detalles internos de base de datos o servidor.
    respuesta_error('No se pudo iniciar sesión.', 500);
}
