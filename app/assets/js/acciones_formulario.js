/**
 * Archivo: acciones_formulario.js
 *
 * Este archivo contiene acciones visuales que aparecen despues de crear,
 * actualizar o eliminar registros. Su objetivo es agregar enlaces de apoyo
 * en los formularios, por ejemplo para volver a ver una tarea.
 *
 * Funciones principales:
 * - Mostrar enlaces posteriores a una operacion exitosa.
 * - Crear o reutilizar la fila de acciones del formulario.
 * - Limpiar acciones anteriores para evitar enlaces duplicados.
 */

/**
 * Proposito:
 * Muestra un enlace para ver una tarea especifica despues de una operacion exitosa.
 *
 * Uso:
 * Se usa en formularios de tareas o subtareas cuando se conoce el id de la tarea relacionada.
 *
 * Argumentos:
 * - formulario {HTMLFormElement|null}: Formulario donde se agregara el enlace.
 * - idTarea {string|number}: Identificador de la tarea que se quiere abrir.
 *
 * Retorna:
 * - {void}: No retorna datos. Modifica el DOM agregando un enlace.
 *
 * Flujo:
 * 1. Verifica que exista el formulario y el id de tarea.
 * 2. Construye la URL hacia el listado de tareas con el id como parametro.
 * 3. Llama a la funcion que agrega la accion visual al formulario.
 */
function mostrarAccionVerTarea(formulario, idTarea) {
    if (!formulario || !idTarea) {
        return;
    }

    agregarAccionFormulario(formulario, 'Ver tarea', `${rutaBase}paginas/tareas_listar.php?id_tarea=${encodeURIComponent(idTarea)}`);
}

/**
 * Proposito:
 * Muestra un enlace para volver al listado general de tareas.
 *
 * Uso:
 * Se usa despues de eliminar una tarea o cuando no corresponde abrir una tarea especifica.
 *
 * Argumentos:
 * - formulario {HTMLFormElement|null}: Formulario donde se agregara el enlace.
 *
 * Retorna:
 * - {void}: No retorna datos. Modifica el DOM agregando un enlace.
 *
 * Flujo:
 * 1. Verifica que exista el formulario.
 * 2. Construye la URL hacia paginas/tareas_listar.php.
 * 3. Llama a la funcion que agrega la accion visual al formulario.
 */
function mostrarAccionVerTareas(formulario) {
    if (!formulario) {
        return;
    }

    agregarAccionFormulario(formulario, 'Ver tareas', `${rutaBase}paginas/tareas_listar.php`);
}

/**
 * Proposito:
 * Agrega un enlace de accion dentro de la fila de botones de un formulario.
 *
 * Uso:
 * Se usa como funcion base para mostrar enlaces posteriores a operaciones exitosas.
 *
 * Argumentos:
 * - formulario {HTMLFormElement}: Formulario que recibira el enlace.
 * - texto {string}: Texto visible y etiqueta accesible del enlace.
 * - url {string}: Direccion a la que apunta el enlace.
 *
 * Retorna:
 * - {void}: No retorna datos. Inserta un enlace en el formulario.
 *
 * Flujo:
 * 1. Limpia enlaces anteriores para evitar duplicados.
 * 2. Obtiene o crea la fila de acciones del formulario.
 * 3. Si la fila existe, crea el enlace.
 * 4. Configura href, dataset y aria-label.
 * 5. Inserta el enlace en la fila de acciones.
 */
function agregarAccionFormulario(formulario, texto, url) {
    limpiarAccionVerTarea(formulario);

    const filaAcciones = obtenerFilaAccionesFormulario(formulario);

    if (!filaAcciones) {
        return;
    }

    const enlace = crearElemento('a', 'button button--soft button--small form-action-row__link', texto);
    enlace.href = url;
    enlace.dataset.accionFormulario = 'ver-tarea';
    enlace.setAttribute('aria-label', texto);
    filaAcciones.appendChild(enlace);
}

/**
 * Proposito:
 * Obtiene la fila visual donde se agrupan los botones de un formulario.
 *
 * Uso:
 * Se usa antes de insertar enlaces secundarios junto al boton principal.
 *
 * Argumentos:
 * - formulario {HTMLFormElement}: Formulario donde se buscara la fila de acciones.
 *
 * Retorna:
 * - {HTMLElement|null}: Fila de acciones encontrada o creada. Retorna null si no hay boton submit.
 *
 * Flujo:
 * 1. Busca el boton principal de tipo submit.
 * 2. Si no existe, retorna null.
 * 3. Busca un contenedor cercano con clase form-action-row.
 * 4. Si no existe o no pertenece al formulario, crea uno nuevo.
 * 5. Mueve el boton principal dentro de la fila y retorna el contenedor.
 */
function obtenerFilaAccionesFormulario(formulario) {
    const botonPrincipal = formulario.querySelector('button[type="submit"]');

    if (!botonPrincipal) {
        return null;
    }

    let filaAcciones = botonPrincipal.closest('.form-action-row');

    if (!filaAcciones || !formulario.contains(filaAcciones)) {
        filaAcciones = crearElemento('div', 'form-action-row');
        botonPrincipal.insertAdjacentElement('beforebegin', filaAcciones);
        filaAcciones.appendChild(botonPrincipal);
    }

    return filaAcciones;
}

/**
 * Proposito:
 * Elimina enlaces o acciones visuales agregadas previamente a un formulario.
 *
 * Uso:
 * Se usa antes de agregar una nueva accion y tambien cuando ocurre un error.
 *
 * Argumentos:
 * - formulario {HTMLFormElement|null}: Formulario donde se limpiaran las acciones.
 *
 * Retorna:
 * - {void}: No retorna datos. Elimina elementos del DOM si existen.
 *
 * Flujo:
 * 1. Busca acciones marcadas como ver-tarea o acciones de exito.
 * 2. Recorre los elementos encontrados.
 * 3. Elimina cada elemento del formulario.
 */
function limpiarAccionVerTarea(formulario) {
    formulario?.querySelectorAll('[data-accion-formulario="ver-tarea"], .form-success-actions').forEach((accion) => {
        accion.remove();
    });
}
