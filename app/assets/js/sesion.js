/**
 * Archivo: sesion.js
 *
 * Este archivo maneja las acciones relacionadas con la sesion del usuario.
 * Controla el inicio de sesion, el registro de cuentas y el cierre de sesion.
 *
 * Funciones principales:
 * - Enviar credenciales al backend.
 * - Registrar usuarios nuevos.
 * - Cerrar sesion y redirigir al inicio.
 */

/**
 * Proposito:
 * Maneja el inicio de sesion del usuario desde el formulario de login.
 *
 * Uso:
 * Se usa cuando se envia el formulario marcado como data-formulario="login".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Envia la peticion y redirige si es correcta.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Convierte los campos del formulario en un objeto.
 * 3. Envia usuario y contrasena al endpoint de inicio de sesion.
 * 4. Si la respuesta es correcta, muestra mensaje y redirige al inicio.
 * 5. Si ocurre un error, muestra el mensaje en pantalla.
 */
async function manejarLogin(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('login');

    try {
        const datos = formularioAObjeto(formulario);
        const respuesta = await peticionApi('api/iniciar_sesion.php', 'POST', datos);
        mostrarMensaje(mensaje, respuesta.mensaje || 'Sesión iniciada correctamente.', 'success');
        window.location.href = `${rutaBase}index.php`;
    } catch (error) {
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

/**
 * Proposito:
 * Maneja el registro de una cuenta nueva desde el formulario de registro.
 *
 * Uso:
 * Se usa cuando se envia el formulario marcado como data-formulario="registro".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Envia la peticion y redirige si es correcta.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Convierte los campos del formulario en un objeto.
 * 3. Envia los datos al endpoint de registro.
 * 4. Si la cuenta se crea, muestra mensaje y redirige al inicio.
 * 5. Si ocurre un error, muestra el mensaje en pantalla.
 */
async function manejarRegistro(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('registro');

    try {
        const datos = formularioAObjeto(formulario);
        const respuesta = await peticionApi('api/registrar_usuario.php', 'POST', datos);
        mostrarMensaje(mensaje, respuesta.mensaje || 'Cuenta creada correctamente.', 'success');
        window.location.href = `${rutaBase}index.php`;
    } catch (error) {
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

/**
 * Proposito:
 * Cierra la sesion actual del usuario.
 *
 * Uso:
 * Se usa al hacer click en botones globales con data-accion="cerrar-sesion".
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Intenta cerrar sesion y redirige al inicio.
 *
 * Flujo:
 * 1. Llama al endpoint api/cerrar_sesion.php.
 * 2. Ignora errores visuales para evitar que el usuario quede atrapado.
 * 3. Redirige al index para volver al estado inicial.
 */
async function cerrarSesion() {
    try {
        await peticionApi('api/cerrar_sesion.php', 'POST', {});
    } catch (error) {
        // Aunque falle el mensaje, se intenta volver al inicio para evitar estados visuales confusos.
    }

    window.location.href = `${rutaBase}index.php`;
}
