// Carga y comportamiento de selectores de tareas y subtareas.

function prepararSelectores() {
    const selectoresTareas = document.querySelectorAll('select[data-select="tareas"]');
    const selectoresSubtareas = document.querySelectorAll('select[data-select="subtareas"]');

    document.querySelectorAll('select[name="id_tarea"]').forEach((selector) => {
        selector.addEventListener('change', manejarCambioTarea);
    });

    document.querySelectorAll('select[name="id_subtarea"]').forEach((selector) => {
        selector.addEventListener('change', manejarCambioSubtarea);
    });

    if (selectoresTareas.length > 0 && selectoresSubtareas.length > 0) {
        Promise.all([cargarTareas(), cargarSubtareas()]).then(() => {
            rellenarSelectoresTareas();
            rellenarSelectoresSubtareas();
        });
        return;
    }

    if (selectoresTareas.length > 0) {
        cargarTareas().then(() => rellenarSelectoresTareas());
    }

    if (selectoresSubtareas.length > 0) {
        cargarSubtareas().then(() => rellenarSelectoresSubtareas());
    }
}

async function manejarCambioTarea(evento) {
    const selector = evento.currentTarget;
    const formulario = selector.closest('form');

    if (!formulario || formulario.dataset.formulario !== 'actualizar-tarea' || !selector.value) {
        return;
    }

    const mensaje = obtenerMensaje('actualizar-tarea');

    try {
        const respuesta = await peticionApi(`api/obtener_tarea.php?id=${encodeURIComponent(selector.value)}`);
        const tarea = respuesta.tarea;
        formulario.elements.titulo.value = tarea.titulo || '';
        formulario.elements.descripcion.value = tarea.descripcion || '';
        limpiarMensaje(mensaje);
    } catch (error) {
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

async function manejarCambioSubtarea(evento) {
    const selector = evento.currentTarget;
    const formulario = selector.closest('form');

    if (!formulario || formulario.dataset.formulario !== 'actualizar-subtarea' || !selector.value) {
        return;
    }

    const mensaje = obtenerMensaje('actualizar-subtarea');

    try {
        const respuesta = await peticionApi(`api/obtener_subtarea.php?id=${encodeURIComponent(selector.value)}`);
        const subtarea = respuesta.subtarea;
        formulario.elements.id_tarea.value = String(subtarea.id_tarea || '');
        formulario.elements.titulo.value = subtarea.titulo || '';
        formulario.elements.descripcion.value = subtarea.descripcion || '';
        formulario.elements.completada.checked = Boolean(subtarea.completada);
        limpiarMensaje(mensaje);
    } catch (error) {
        mostrarMensaje(mensaje, error.message, 'error');
    }
}

function rellenarSelectoresTareas(valorSeleccionado = '', opciones = {}) {
    const dispararCambio = opciones.dispararCambio !== false;

    document.querySelectorAll('select[data-select="tareas"]').forEach((selector) => {
        const formulario = selector.closest('form');
        const tipoFormulario = formulario?.dataset.formulario || '';
        const valorDesdeUrl = ['crear-subtarea', 'actualizar-tarea', 'eliminar-tarea'].includes(tipoFormulario)
            ? obtenerParametroUrl('id_tarea')
            : '';
        const valorActual = valorSeleccionado || selector.value || valorDesdeUrl;
        selector.replaceChildren(crearOpcion('', estado.tareas.length > 0 ? 'Selecciona una tarea' : 'No hay tareas registradas'));

        estado.tareas.forEach((tarea) => {
            selector.appendChild(crearOpcion(String(tarea.id), tarea.titulo));
        });

        selector.value = valorActual;

        if (!selector.value && estado.tareas.length > 0 && tipoFormulario === 'actualizar-tarea') {
            selector.value = String(estado.tareas[0].id);
        }

        if (dispararCambio && selector.value && tipoFormulario === 'actualizar-tarea') {
            selector.dispatchEvent(new Event('change'));
        }
    });
}

function rellenarSelectoresSubtareas(valorSeleccionado = '', opciones = {}) {
    const dispararCambio = opciones.dispararCambio !== false;

    document.querySelectorAll('select[data-select="subtareas"]').forEach((selector) => {
        const formulario = selector.closest('form');
        const tipoFormulario = formulario?.dataset.formulario || '';
        const valorDesdeUrl = ['actualizar-subtarea', 'eliminar-subtarea'].includes(tipoFormulario)
            ? obtenerParametroUrl('id_subtarea')
            : '';
        const valorActual = valorSeleccionado || selector.value || valorDesdeUrl;
        selector.replaceChildren(crearOpcion('', estado.subtareas.length > 0 ? 'Selecciona una subtarea' : 'No hay subtareas registradas'));

        estado.subtareas.forEach((subtarea) => {
            selector.appendChild(crearOpcion(String(subtarea.id), `${subtarea.titulo} — ${subtarea.titulo_tarea || 'Tarea'}`));
        });

        selector.value = valorActual;

        if (!selector.value && estado.subtareas.length > 0 && tipoFormulario === 'actualizar-subtarea') {
            selector.value = String(estado.subtareas[0].id);
        }

        if (dispararCambio && selector.value && tipoFormulario === 'actualizar-subtarea') {
            selector.dispatchEvent(new Event('change'));
        }
    });
}
