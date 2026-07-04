// Formularios para crear, actualizar y eliminar subtareas.

async function manejarCrearSubtarea(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('crear-subtarea');
    limpiarAccionVerTarea(formulario);

    try {
        const datos = formularioAObjeto(formulario);
        const respuesta = await peticionApi('api/crear_subtarea.php', 'POST', datos);
        const idTarea = datos.id_tarea;
        formulario.reset();
        const selectorTarea = formulario.querySelector('[name="id_tarea"]');
        if (selectorTarea) {
            selectorTarea.value = idTarea;
        }
        mostrarMensaje(mensaje, respuesta.mensaje || 'Subtarea creada correctamente.', 'success');
        mostrarAccionVerTarea(formulario, idTarea);
    } catch (error) {
        limpiarAccionVerTarea(formulario);
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

async function manejarActualizarSubtarea(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('actualizar-subtarea');
    limpiarAccionVerTarea(formulario);

    try {
        const datos = formularioAObjeto(formulario);
        datos.completada = formulario.querySelector('[name="completada"]')?.checked || false;
        const respuesta = await peticionApi('api/actualizar_subtarea.php', 'POST', datos);
        await cargarSubtareas();
        rellenarSelectoresSubtareas(datos.id_subtarea, { dispararCambio: false });
        mostrarMensaje(mensaje, respuesta.mensaje || 'Subtarea actualizada correctamente.', 'success');
        mostrarAccionVerTarea(formulario, datos.id_tarea);
    } catch (error) {
        limpiarAccionVerTarea(formulario);
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

async function manejarEliminarSubtarea(evento) {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const mensaje = obtenerMensaje('eliminar-subtarea');
    const datos = formularioAObjeto(formulario);
    const subtareaSeleccionada = estado.subtareas.find((subtarea) => String(subtarea.id) === String(datos.id_subtarea));
    const idTarea = subtareaSeleccionada?.id_tarea || obtenerParametroUrl('id_tarea');
    limpiarAccionVerTarea(formulario);

    if (!datos.id_subtarea) {
        mostrarMensaje(mensaje, 'Debes seleccionar una subtarea.', 'error');
        return;
    }

    if (!confirm('¿Seguro que deseas eliminar esta subtarea?')) {
        return;
    }

    try {
        const respuesta = await peticionApi('api/eliminar_subtarea.php', 'POST', datos);
        await cargarSubtareas();
        await cargarTareas();
        rellenarSelectoresSubtareas();
        mostrarMensaje(mensaje, respuesta.mensaje || 'Subtarea eliminada correctamente.', 'success');
        mostrarAccionVerTarea(formulario, idTarea);
    } catch (error) {
        limpiarAccionVerTarea(formulario);
        mostrarMensaje(mensaje, error.message, 'error');
    }
}
