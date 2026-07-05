<?php
declare(strict_types=1);

/**
 * Archivo: registrar_usuario.php
 *
 * Proposito:
 * Endpoint encargado de crear una cuenta de usuario nueva.
 *
 * Uso:
 * Se llama desde el formulario de registro del frontend mediante una peticion POST.
 *
 * Metodo esperado:
 * - POST
 *
 * Entrada esperada:
 * - nombre {string}: Nombre del usuario.
 * - correo {string}: Correo unico de la cuenta.
 * - contrasena {string}: Contrasena que sera hasheada antes de guardarse.
 *
 * Salida:
 * - JSON de exito con los datos basicos del usuario creado.
 * - JSON de error si el correo ya existe o si los datos son invalidos.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion POST.
 * 3. Lee y normaliza nombre, correo y contrasena.
 * 4. Valida formato de correo y largo minimo de contrasena.
 * 5. Verifica que el correo no este registrado.
 * 6. Inserta el usuario con password_hash.
 * 7. Guarda el usuario en sesion.
 * 8. Registra la creacion en auditoria.
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
   Lectura y normalizacion de datos
   ============================================================ */

// Datos enviados desde el formulario de registro.
$datos = obtener_entrada_json();
// Nombre del usuario que se guardara en la cuenta.
$nombre = normalizar_texto_obligatorio($datos['nombre'] ?? null, 120, 'El nombre');
// Correo normalizado a minusculas para evitar duplicados por mayusculas.
$correo = strtolower(normalizar_texto_obligatorio($datos['correo'] ?? null, 180, 'El correo'));
// Contrasena original. Nunca se guarda directamente en la base de datos.
$contrasena = valor_a_texto($datos['contrasena'] ?? null);
// Rol por defecto para cuentas creadas desde el formulario publico.
$rol = 'usuario';

/* ============================================================
   Validaciones de cuenta
   ============================================================ */

// Valida que el correo tenga formato correcto.
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    respuesta_error('El correo no tiene un formato válido.', 422);
}

// Valida largo minimo de contrasena antes de hashearla.
if (strlen($contrasena) < 6) {
    respuesta_error('La contraseña debe tener al menos 6 caracteres.', 422);
}


/* ============================================================
   Creacion del usuario
   ============================================================ */

try {
    // Revisa si el correo ya esta registrado.
    $consultaCorreo = $conexion->prepare('SELECT id FROM usuarios WHERE correo = :correo LIMIT 1');
    $consultaCorreo->execute([':correo' => $correo]);

    // Si el correo existe, se detiene el registro.
    if ($consultaCorreo->fetchColumn() !== false) {
        respuesta_error('Ya existe una cuenta registrada con ese correo.', 409);
    }

    // Inserta el usuario usando password_hash para proteger la contrasena.
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

    // ID generado automaticamente por MySQL para el nuevo usuario.
    $idUsuario = (int) $conexion->lastInsertId();
    // Inicia sesion inmediatamente despues de registrar la cuenta.
    $_SESSION['usuario'] = [
        'id' => $idUsuario,
        'nombre' => $nombre,
        'correo' => $correo,
        'rol' => $rol,
    ];

    // Registra la creacion del usuario en auditoria.
    registrar_log($conexion, $idUsuario, 'creacion', 'usuarios', $idUsuario, 'Usuario registrado: ' . $nombre);

    // Devuelve al frontend el usuario creado.
    respuesta_exitosa([
        'mensaje' => 'Cuenta creada correctamente.',
        'usuario' => $_SESSION['usuario'],
    ], 201);
} catch (Throwable $excepcion) {
    // Respuesta generica para no exponer detalles internos.
    respuesta_error('No se pudo registrar el usuario.', 500);
}
