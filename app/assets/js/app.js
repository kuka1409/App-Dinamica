// Inicializador principal del frontend.
document.addEventListener('DOMContentLoaded', () => {
    registrarEventosGlobales();
    prepararFormularios();
    prepararSelectores();
    prepararListados();
});

function registrarEventosGlobales() {
    document.querySelectorAll('[data-accion="cerrar-sesion"]').forEach((boton) => {
        boton.addEventListener('click', cerrarSesion);
    });
}

function prepararFormularios() {
    const acciones = {
        login: manejarLogin,
        registro: manejarRegistro,
        'crear-tarea': manejarCrearTarea,
        'actualizar-tarea': manejarActualizarTarea,
        'eliminar-tarea': manejarEliminarTarea,
        'crear-subtarea': manejarCrearSubtarea,
        'actualizar-subtarea': manejarActualizarSubtarea,
        'eliminar-subtarea': manejarEliminarSubtarea,
    };

    document.querySelectorAll('[data-formulario]').forEach((formulario) => {
        const tipo = formulario.dataset.formulario;
        const accion = acciones[tipo];

        if (accion) {
            formulario.addEventListener('submit', accion);
        }
    });
}

function prepararListados() {
    const listaTareas = document.querySelector('[data-lista="tareas"]');
    const listaSubtareas = document.querySelector('[data-lista="subtareas"]');
    const listaLogs = document.querySelector('[data-lista="logs"]');

    if (listaTareas) {
        listaTareas.addEventListener('click', manejarClicListadoTareas);
        listaTareas.addEventListener('change', manejarCambioListadoTareas);
        cargarTareas().then(() => {
            expandirTareaDesdeUrl();
            mostrarTareas(listaTareas);
            enfocarTareaDesdeUrl(listaTareas);
        });
    }

    if (listaSubtareas) {
        cargarSubtareas().then(() => mostrarSubtareas(listaSubtareas));
    }

    if (listaLogs) {
        cargarLogs().then((logs) => mostrarLogs(listaLogs, logs));
    }
}
