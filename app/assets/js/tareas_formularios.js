// Formularios para crear, actualizar y eliminar tareas.

async function manejarCrearTarea(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('crear-tarea');
    limpiarAccionVerTarea(formulario);

    try {
        const datos = formularioAObjeto(formulario);
        const respuesta = await peticionApi('api/crear_tarea.php', 'POST', datos);
        formulario.reset();
        mostrarMensaje(mensaje, respuesta.mensaje || 'Tarea creada correctamente.', 'success');
        mostrarAccionVerTarea(formulario, respuesta.id_tarea);
    } catch (error) {
        limpiarAccionVerTarea(formulario);
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

async function manejarActualizarTarea(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('actualizar-tarea');
    limpiarAccionVerTarea(formulario);

    try {
        const datos = formularioAObjeto(formulario);
        const respuesta = await peticionApi('api/actualizar_tarea.php', 'POST', datos);
        await cargarTareas();
        rellenarSelectoresTareas(datos.id_tarea, { dispararCambio: false });
        mostrarMensaje(mensaje, respuesta.mensaje || 'Tarea actualizada correctamente.', 'success');
        mostrarAccionVerTarea(formulario, datos.id_tarea);
    } catch (error) {
        limpiarAccionVerTarea(formulario);
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

async function manejarEliminarTarea(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('eliminar-tarea');
    const datos = formularioAObjeto(formulario);
    limpiarAccionVerTarea(formulario);

    if (!datos.id_tarea) {
        mostrarMensaje(mensaje, 'Debes seleccionar una tarea.', 'error');
        return;
    }

    if (!confirm('¿Seguro que deseas eliminar esta tarea y sus subtareas?')) {
        return;
    }

    try {
        const respuesta = await peticionApi('api/eliminar_tarea.php', 'POST', datos);
        await cargarTareas();
        await cargarSubtareas();
        rellenarSelectoresTareas();
        rellenarSelectoresSubtareas();
        mostrarMensaje(mensaje, respuesta.mensaje || 'Tarea eliminada correctamente.', 'success');
        mostrarAccionVerTareas(formulario);
    } catch (error) {
        limpiarAccionVerTarea(formulario);
        mostrarMensaje(mensaje, error.message, 'error');
    }
}
