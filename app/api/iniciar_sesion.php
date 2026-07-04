<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');

$datos = obtener_entrada_json();
$correo = strtolower(normalizar_texto_obligatorio($datos['correo'] ?? null, 180, 'El correo'));
$contrasena = valor_a_texto($datos['contrasena'] ?? null);

try {
    $consulta = $conexion->prepare(
        'SELECT id, nombre, correo, contrasena_hash, rol
         FROM usuarios
         WHERE correo = :correo
         LIMIT 1'
    );
    $consulta->execute([':correo' => $correo]);
    $usuario = $consulta->fetch();

    if (!$usuario || !password_verify($contrasena, (string) $usuario['contrasena_hash'])) {
        registrar_log($conexion, null, 'inicio_sesion', 'Inicio sesión', 'usuarios', null, 'Intento fallido de inicio de sesión para correo: ' . $correo);
        respuesta_error('Correo o contraseña incorrectos.', 401);
    }

    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id' => (int) $usuario['id'],
        'nombre' => (string) $usuario['nombre'],
        'correo' => (string) $usuario['correo'],
        'rol' => (string) $usuario['rol'],
    ];

    registrar_log($conexion, (int) $usuario['id'], 'inicio_sesion', 'Inicio sesión', 'usuarios', (int) $usuario['id'], 'Usuario inició sesión');

    respuesta_exitosa([
        'mensaje' => 'Sesión iniciada correctamente.',
        'usuario' => $_SESSION['usuario'],
    ]);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo iniciar sesión.', 500);
}
