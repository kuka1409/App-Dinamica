/**
 * Archivo: datos.js
 *
 * Este archivo se encarga de cargar datos desde la API y guardarlos en el
 * estado compartido del frontend. Actua como capa intermedia entre el backend
 * y los componentes visuales.
 *
 * Funciones principales:
 * - Cargar tareas.
 * - Cargar subtareas.
 * - Cargar logs de auditoria.
 * - Mantener limpio el estado de tareas expandidas.
 */

/**
 * Proposito:
 * Carga las tareas del usuario desde la API y las guarda en el estado global.
 *
 * Uso:
 * Se usa antes de renderizar tareas o rellenar selectores de tareas.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {Promise<Array>}: Lista de tareas guardada en estado.tareas.
 *
 * Flujo:
 * 1. Solicita las tareas al endpoint api/listar_tareas.php.
 * 2. Verifica que la respuesta contenga un arreglo.
 * 3. Guarda el resultado en estado.tareas.
 * 4. Sincroniza las tareas expandidas para quitar ids inexistentes.
 * 5. Retorna la lista de tareas.
 */
async function cargarTareas() {
    const respuesta = await peticionApi('api/listar_tareas.php');
    estado.tareas = Array.isArray(respuesta.tareas) ? respuesta.tareas : [];
    sincronizarTareasExpandidas();
    return estado.tareas;
}

/**
 * Proposito:
 * Elimina del estado los ids de tareas expandidas que ya no existen.
 *
 * Uso:
 * Se usa despues de cargar tareas para mantener coherente la interfaz.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {void}: No retorna datos. Modifica estado.idsTareasExpandidas si corresponde.
 *
 * Flujo:
 * 1. Crea un conjunto con los ids de tareas disponibles.
 * 2. Recorre los ids actualmente expandidos.
 * 3. Si un id ya no existe en las tareas, lo elimina del conjunto de expandidas.
 */
function sincronizarTareasExpandidas() {
    const idsDisponibles = new Set(estado.tareas.map((tarea) => Number(tarea.id)));

    estado.idsTareasExpandidas.forEach((idTarea) => {
        if (!idsDisponibles.has(Number(idTarea))) {
            estado.idsTareasExpandidas.delete(idTarea);
        }
    });
}

/**
 * Proposito:
 * Carga las subtareas desde la API y las guarda en el estado global.
 *
 * Uso:
 * Se usa antes de renderizar subtareas o rellenar selectores de subtareas.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {Promise<Array>}: Lista de subtareas guardada en estado.subtareas.
 *
 * Flujo:
 * 1. Solicita las subtareas al endpoint api/listar_subtareas.php.
 * 2. Verifica que la respuesta contenga un arreglo.
 * 3. Guarda el resultado en estado.subtareas.
 * 4. Retorna la lista de subtareas.
 */
async function cargarSubtareas() {
    const respuesta = await peticionApi('api/listar_subtareas.php');
    estado.subtareas = Array.isArray(respuesta.subtareas) ? respuesta.subtareas : [];
    return estado.subtareas;
}

/**
 * Proposito:
 * Carga los registros de auditoria desde la API usando filtros opcionales.
 *
 * Uso:
 * Se usa desde auditoria.js para obtener los datos que luego se renderizan en tabla.
 *
 * Argumentos:
 * - filtros {object}: Filtros opcionales para limitar los logs consultados.
 *
 * Retorna:
 * - {Promise<Array>}: Lista de logs o arreglo vacio si ocurre un error.
 *
 * Flujo:
 * 1. Obtiene el contenedor de mensajes de auditoria.
 * 2. Convierte los filtros con valor en parametros de URL.
 * 3. Construye la URL final del endpoint.
 * 4. Solicita los logs al backend.
 * 5. Si la respuesta es correcta, limpia mensajes y retorna los logs.
 * 6. Si ocurre un error, muestra el mensaje y retorna arreglo vacio.
 */
async function cargarLogs(filtros = {}) {
    const mensaje = obtenerMensaje('auditoria');
    const parametros = new URLSearchParams();

    Object.entries(filtros).forEach(([clave, valor]) => {
        const texto = String(valor || '').trim();

        if (texto !== '') {
            parametros.append(clave, texto);
        }
    });

    const consulta = parametros.toString();
    const url = consulta ? `api/listar_logs.php?${consulta}` : 'api/listar_logs.php';

    try {
        const respuesta = await peticionApi(url);
        limpiarMensaje(mensaje);
        return Array.isArray(respuesta.logs) ? respuesta.logs : [];
    } catch (error) {
        mostrarMensaje(mensaje, error.message, 'error');
        return [];
    }
}
