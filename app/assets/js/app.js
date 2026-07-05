/**
 * Archivo: app.js
 *
 * Este archivo es el inicializador principal del frontend. Se ejecuta cuando el
 * HTML ya esta cargado y conecta la interfaz con los distintos modulos JS.
 *
 * Funciones principales:
 * - Registrar eventos globales.
 * - Conectar formularios con sus manejadores.
 * - Preparar selectores dinamicos.
 * - Cargar listados segun la pagina actual.
 */

/**
 * Proposito:
 * Espera a que el HTML termine de cargarse antes de iniciar el frontend.
 *
 * Uso:
 * Es el primer flujo JavaScript que corre en el navegador para esta aplicacion.
 *
 * Flujo:
 * 1. Registra eventos globales.
 * 2. Prepara formularios.
 * 3. Prepara selectores.
 * 4. Prepara listados segun la pagina actual.
 */
document.addEventListener('DOMContentLoaded', () => {
    registrarEventosGlobales();
    prepararFormularios();
    prepararSelectores();
    prepararListados();
});

/**
 * Proposito:
 * Registra eventos que pueden existir en distintas paginas del sistema.
 *
 * Uso:
 * Se ejecuta al cargar el frontend para activar botones globales como cerrar sesion.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {void}: No retorna datos. Solo registra listeners en el DOM.
 *
 * Flujo:
 * 1. Busca botones con data-accion="cerrar-sesion".
 * 2. Recorre cada boton encontrado.
 * 3. Asocia el evento click con la funcion cerrarSesion.
 */
function registrarEventosGlobales() {
    document.querySelectorAll('[data-accion="cerrar-sesion"]').forEach((boton) => {
        boton.addEventListener('click', cerrarSesion);
    });
}

/**
 * Proposito:
 * Conecta cada formulario HTML con la funcion JavaScript que debe procesarlo.
 *
 * Uso:
 * Se ejecuta al cargar la pagina para activar login, registro, tareas y subtareas.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {void}: No retorna datos. Registra eventos submit en formularios.
 *
 * Flujo:
 * 1. Define un mapa entre tipos de formulario y funciones manejadoras.
 * 2. Busca todos los elementos con data-formulario.
 * 3. Lee el tipo de formulario desde dataset.
 * 4. Si existe una funcion para ese tipo, la conecta al evento submit.
 */
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

/**
 * Proposito:
 * Inicializa los listados visibles de tareas, subtareas y logs de auditoria.
 *
 * Uso:
 * Se ejecuta al cargar la pagina y solo activa el listado que exista en el HTML actual.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {void}: No retorna datos. Carga datos y registra eventos en los listados.
 *
 * Flujo:
 * 1. Busca contenedores marcados con data-lista.
 * 2. Si hay listado de tareas, registra eventos y carga tareas.
 * 3. Si hay listado de subtareas, carga subtareas y las muestra.
 * 4. Si hay listado de logs, prepara la vista de auditoria.
 */
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
        prepararAuditoria(listaLogs);
    }
}
