// Renderizado de la tabla de auditoría para usuarios auditores.

function mostrarLogs(contenedor, logs) {
    contenedor.replaceChildren();

    if (!logs || logs.length === 0) {
        contenedor.appendChild(crearEstadoVacio('Aún no hay registros de auditoría.'));
        return;
    }

    const envoltorio = crearElemento('div', 'logs-table-wrapper');
    const tabla = crearElemento('table', 'logs-table');
    const encabezado = document.createElement('thead');
    const filaEncabezado = document.createElement('tr');

    ['Fecha', 'Hora', 'Usuario', 'Movimiento', 'Ubicación', 'Tabla', 'Registro', 'IP_HOST_CLIENTE', 'Sistema', 'Navegador', 'Detalle'].forEach((titulo) => {
        filaEncabezado.appendChild(crearElemento('th', '', titulo));
    });

    encabezado.appendChild(filaEncabezado);

    const cuerpo = document.createElement('tbody');

    logs.forEach((log) => {
        const fila = document.createElement('tr');
        const usuario = log.usuario_nombre
            ? `${log.usuario_nombre} (${log.usuario_rol || 'sin rol'})`
            : 'Usuario eliminado o no disponible';
        const fechaEvento = log.fecha_evento || 'Sin fecha';

        [
            fechaEvento,
            log.hora_evento || 'Sin hora',
            usuario,
            formatearMovimientoLog(log.tipo_movimiento),
            log.ubicacion || 'Sin ubicación',
            log.tabla_afectada || 'N/A',
            log.id_registro || 'N/A',
            log.ip || 'N/A',
            log.sistema_operativo || 'No identificado',
            log.navegador || 'No identificado',
            log.detalle || 'Sin detalle.',
        ].forEach((valor, indice) => {
            const celda = crearElemento('td', indice === 10 ? 'logs-table__detail' : '', valor);
            fila.appendChild(celda);
        });

        cuerpo.appendChild(fila);
    });

    tabla.append(encabezado, cuerpo);
    envoltorio.appendChild(tabla);
    contenedor.appendChild(envoltorio);
}

function formatearMovimientoLog(tipoMovimiento) {
    const etiquetas = {
        creacion: 'Creación',
        actualizacion: 'Actualización',
        eliminacion: 'Eliminación',
        lectura: 'Lectura',
        inicio_sesion: 'Inicio sesión',
        cierre_sesion: 'Cierre sesión',
    };

    return etiquetas[tipoMovimiento] || tipoMovimiento || 'Sin movimiento';
}
