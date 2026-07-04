<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('POST');

$datos = obtener_entrada_json();
$nombre = normalizar_texto_obligatorio($datos['nombre'] ?? null, 120, 'El nombre');
$correo = strtolower(normalizar_texto_obligatorio($datos['correo'] ?? null, 180, 'El correo'));
$contrasena = valor_a_texto($datos['contrasena'] ?? null);
$rol = 'usuario';

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    respuesta_error('El correo no tiene un formato válido.', 422);
}

if (strlen($contrasena) < 6) {
    respuesta_error('La contraseña debe tener al menos 6 caracteres.', 422);
}


try {
    $consultaCorreo = $conexion->prepare('SELECT id FROM usuarios WHERE correo = :correo LIMIT 1');
    $consultaCorreo->execute([':correo' => $correo]);

    if ($consultaCorreo->fetchColumn() !== false) {
        respuesta_error('Ya existe una cuenta registrada con ese correo.', 409);
    }

    $consulta = $conexion->prepare(
        'INSERT INTO usuarios (nombre, correo, contrasena_hash, rol)
         VALUES (:nombre, :correo, :contrasena_hash, :rol)'
    );
    $consulta->execute([
        ':nombre' => $nombre,
        ':correo' => $correo,
        ':contrasena_hash' => password_hash($contrasena, PASSWORD_DEFAULT),
        ':rol' => $rol,
    ]);

    $idUsuario = (int) $conexion->lastInsertId();
    $_SESSION['usuario'] = [
        'id' => $idUsuario,
        'nombre' => $nombre,
        'correo' => $correo,
        'rol' => $rol,
    ];

    registrar_log($conexion, $idUsuario, 'creacion', 'Creación de usuario', 'usuarios', $idUsuario, 'Usuario registrado: ' . $nombre);

    respuesta_exitosa([
        'mensaje' => 'Cuenta creada correctamente.',
        'usuario' => $_SESSION['usuario'],
    ], 201);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudo registrar el usuario.', 500);
}
