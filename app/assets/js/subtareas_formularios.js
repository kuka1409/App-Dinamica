/**
 * Archivo: subtareas_formularios.js
 *
 * Este archivo maneja los formularios de creacion, actualizacion y eliminacion
 * de subtareas. Se comunica con la API y actualiza selectores o mensajes segun
 * el resultado de cada operacion.
 *
 * Funciones principales:
 * - Crear subtareas.
 * - Actualizar subtareas.
 * - Eliminar subtareas.
 * - Mostrar enlaces para volver a la tarea asociada.
 */

/**
 * Proposito:
 * Procesa el formulario para crear una nueva subtarea.
 *
 * Uso:
 * Se usa cuando se envia el formulario data-formulario="crear-subtarea".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Crea la subtarea y actualiza el formulario.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Limpia acciones visuales anteriores.
 * 3. Convierte el formulario en objeto.
 * 4. Envia los datos al endpoint de creacion de subtarea.
 * 5. Reinicia el formulario conservando la tarea asociada.
 * 6. Muestra mensaje y enlace para ver la tarea.
 */
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

/**
 * Proposito:
 * Procesa el formulario para actualizar una subtarea existente.
 *
 * Uso:
 * Se usa cuando se envia el formulario data-formulario="actualizar-subtarea".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Actualiza la subtarea y refresca selectores.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Limpia acciones visuales anteriores.
 * 3. Convierte el formulario en objeto.
 * 4. Agrega el estado del checkbox completada.
 * 5. Envia los datos al endpoint de actualizacion.
 * 6. Recarga subtareas, actualiza el selector y muestra mensaje.
 */
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

/**
 * Proposito:
 * Procesa el formulario para eliminar una subtarea seleccionada.
 *
 * Uso:
 * Se usa cuando se envia el formulario data-formulario="eliminar-subtarea".
 *
 * Argumentos:
 * - evento {SubmitEvent}: Evento generado al enviar el formulario.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Elimina la subtarea y refresca datos.
 *
 * Flujo:
 * 1. Detiene el envio normal del formulario.
 * 2. Obtiene los datos y localiza la tarea asociada.
 * 3. Valida que exista una subtarea seleccionada.
 * 4. Solicita confirmacion al usuario.
 * 5. Envia la eliminacion al backend.
 * 6. Recarga tareas y subtareas, actualiza selectores y muestra mensaje.
 */
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
