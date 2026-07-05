/**
 * Archivo: tareas_formularios.js
 *
 * Este archivo maneja los formularios de creacion, actualizacion y eliminacion
 * de tareas. Cada funcion toma datos del formulario, llama a la API y actualiza
 * la interfaz segun la respuesta.
 *
 * Funciones principales:
 * - Crear tareas.
 * - Actualizar tareas.
 * - Eliminar tareas junto con sus subtareas.
 * - Refrescar datos y selectores despues de cada cambio.
 */

/**
 * Proposito:
 * Procesa el formulario para crear una nueva tarea.
 *
 * Uso:
 * Se usa cuando se envia el formulario data-formulario="crear-tarea".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Crea la tarea y muestra acciones posteriores.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Limpia acciones visuales anteriores.
 * 3. Convierte el formulario en objeto.
 * 4. Envia los datos al endpoint de creacion.
 * 5. Reinicia el formulario.
 * 6. Muestra mensaje y enlace para ver la tarea creada.
 */
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

/**
 * Proposito:
 * Procesa el formulario para actualizar una tarea existente.
 *
 * Uso:
 * Se usa cuando se envia el formulario data-formulario="actualizar-tarea".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Actualiza la tarea y refresca datos.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Limpia acciones visuales anteriores.
 * 3. Convierte el formulario en objeto.
 * 4. Envia los datos al endpoint de actualizacion.
 * 5. Recarga tareas y actualiza el selector.
 * 6. Muestra mensaje y enlace para ver la tarea.
 */
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

/**
 * Proposito:
 * Procesa el formulario para eliminar una tarea seleccionada.
 *
 * Uso:
 * Se usa cuando se envia el formulario data-formulario="eliminar-tarea".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Elimina la tarea y refresca selectores.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Obtiene los datos del formulario.
 * 3. Valida que exista una tarea seleccionada.
 * 4. Solicita confirmacion al usuario.
 * 5. Envia la eliminacion al backend.
 * 6. Recarga tareas y subtareas, actualiza selectores y muestra mensaje.
 */
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
