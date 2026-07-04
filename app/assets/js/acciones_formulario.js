// Acciones visuales posteriores a crear, modificar o eliminar registros.

function mostrarAccionVerTarea(formulario, idTarea) {
    if (!formulario || !idTarea) {
        return;
    }

    agregarAccionFormulario(formulario, 'Ver tarea', `${rutaBase}paginas/tareas_listar.php?id_tarea=${encodeURIComponent(idTarea)}`);
}

function mostrarAccionVerTareas(formulario) {
    if (!formulario) {
        return;
    }

    agregarAccionFormulario(formulario, 'Ver tareas', `${rutaBase}paginas/tareas_listar.php`);
}

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

function limpiarAccionVerTarea(formulario) {
    formulario?.querySelectorAll('[data-accion-formulario="ver-tarea"], .form-success-actions').forEach((accion) => {
        accion.remove();
    });
}
