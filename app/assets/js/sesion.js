// Formularios y acciones de sesión.

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

async function cerrarSesion() {
    try {
        await peticionApi('api/cerrar_sesion.php', 'POST', {});
    } catch (error) {
        // Aunque falle el mensaje, se intenta volver al inicio para evitar estados visuales confusos.
    }

    window.location.href = `${rutaBase}index.php`;
}
