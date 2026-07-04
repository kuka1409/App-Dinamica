// Carga de datos desde la API y actualización del estado compartido.

async function cargarTareas() {
    const respuesta = await peticionApi('api/listar_tareas.php');
    estado.tareas = Array.isArray(respuesta.tareas) ? respuesta.tareas : [];
    sincronizarTareasExpandidas();
    return estado.tareas;
}

function sincronizarTareasExpandidas() {
    const idsDisponibles = new Set(estado.tareas.map((tarea) => Number(tarea.id)));

    estado.idsTareasExpandidas.forEach((idTarea) => {
        if (!idsDisponibles.has(Number(idTarea))) {
            estado.idsTareasExpandidas.delete(idTarea);
        }
    });
}

async function cargarSubtareas() {
    const respuesta = await peticionApi('api/listar_subtareas.php');
    estado.subtareas = Array.isArray(respuesta.subtareas) ? respuesta.subtareas : [];
    return estado.subtareas;
}

async function cargarLogs() {
    const mensaje = obtenerMensaje('auditoria');

    try {
        const respuesta = await peticionApi('api/listar_logs.php');
        limpiarMensaje(mensaje);
        return Array.isArray(respuesta.logs) ? respuesta.logs : [];
    } catch (error) {
        mostrarMensaje(mensaje, error.message, 'error');
        return [];
    }
}
