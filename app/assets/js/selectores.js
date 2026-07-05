/**
 * Archivo: selectores.js
 *
 * Este archivo prepara y actualiza los selectores de tareas y subtareas.
 * Tambien rellena formularios de actualizacion cuando el usuario cambia
 * una opcion en un selector.
 *
 * Funciones principales:
 * - Preparar eventos change.
 * - Cargar opciones desde el estado.
 * - Rellenar selectores dinamicos.
 * - Completar formularios con datos obtenidos desde la API.
 */

/**
 * Proposito:
 * Prepara los selectores de tareas y subtareas disponibles en la pagina actual.
 *
 * Uso:
 * Se ejecuta al iniciar el frontend para cargar opciones y registrar eventos change.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {void}: No retorna datos. Carga datos y rellena selectores.
 *
 * Flujo:
 * 1. Busca selectores de tareas y subtareas.
 * 2. Registra eventos change en selectores por nombre.
 * 3. Si existen ambos tipos, carga tareas y subtareas en paralelo.
 * 4. Si existe solo un tipo, carga solo los datos necesarios.
 * 5. Rellena las opciones visibles en los selectores.
 */
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

/**
 * Proposito:
 * Carga los datos de una tarea seleccionada y completa el formulario de actualizacion.
 *
 * Uso:
 * Se usa cuando cambia un selector de id_tarea dentro del formulario actualizar-tarea.
 *
 * Argumentos:
 * - evento {Event}: Evento change generado por el selector de tareas.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Completa campos del formulario.
 *
 * Flujo:
 * 1. Obtiene el selector que disparo el evento.
 * 2. Verifica que pertenezca al formulario correcto y tenga valor.
 * 3. Solicita la tarea seleccionada al backend.
 * 4. Copia titulo y descripcion en el formulario.
 * 5. Limpia mensajes si todo sale bien o muestra error si falla.
 */
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

/**
 * Proposito:
 * Carga los datos de una subtarea seleccionada y completa el formulario de actualizacion.
 *
 * Uso:
 * Se usa cuando cambia un selector de id_subtarea dentro del formulario actualizar-subtarea.
 *
 * Argumentos:
 * - evento {Event}: Evento change generado por el selector de subtareas.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Completa campos del formulario.
 *
 * Flujo:
 * 1. Obtiene el selector que disparo el evento.
 * 2. Verifica que pertenezca al formulario correcto y tenga valor.
 * 3. Solicita la subtarea seleccionada al backend.
 * 4. Copia tarea asociada, titulo, descripcion y estado completado.
 * 5. Limpia mensajes si todo sale bien o muestra error si falla.
 */
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

/**
 * Proposito:
 * Rellena todos los selectores de tareas con las tareas disponibles en el estado global.
 *
 * Uso:
 * Se usa despues de cargar tareas o despues de crear, editar o eliminar registros.
 *
 * Argumentos:
 * - valorSeleccionado {string|number}: Valor que debe quedar seleccionado si existe.
 * - opciones {object}: Opciones de comportamiento, como dispararCambio.
 *
 * Retorna:
 * - {void}: No retorna datos. Actualiza opciones de selectores en el DOM.
 *
 * Flujo:
 * 1. Define si debe disparar el evento change automaticamente.
 * 2. Recorre todos los selectores marcados como tareas.
 * 3. Calcula el valor actual desde parametro, selector o URL.
 * 4. Limpia opciones anteriores y agrega una opcion inicial.
 * 5. Inserta una opcion por cada tarea disponible.
 * 6. Selecciona el valor correspondiente y dispara change si aplica.
 */
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

/**
 * Proposito:
 * Rellena todos los selectores de subtareas con las subtareas disponibles en el estado global.
 *
 * Uso:
 * Se usa despues de cargar subtareas o despues de crear, editar o eliminar registros.
 *
 * Argumentos:
 * - valorSeleccionado {string|number}: Valor que debe quedar seleccionado si existe.
 * - opciones {object}: Opciones de comportamiento, como dispararCambio.
 *
 * Retorna:
 * - {void}: No retorna datos. Actualiza opciones de selectores en el DOM.
 *
 * Flujo:
 * 1. Define si debe disparar el evento change automaticamente.
 * 2. Recorre todos los selectores marcados como subtareas.
 * 3. Calcula el valor actual desde parametro, selector o URL.
 * 4. Limpia opciones anteriores y agrega una opcion inicial.
 * 5. Inserta una opcion por cada subtarea disponible.
 * 6. Selecciona el valor correspondiente y dispara change si aplica.
 */
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
