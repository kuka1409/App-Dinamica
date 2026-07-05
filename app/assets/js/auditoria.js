/**
 * Archivo: auditoria.js
 *
 * Este archivo maneja la vista de auditoria. Controla los filtros del log,
 * consulta los registros al backend y renderiza la tabla de movimientos.
 *
 * Funciones principales:
 * - Preparar eventos de los filtros.
 * - Obtener filtros desde el formulario.
 * - Cargar logs desde la API.
 * - Mostrar registros en tabla.
 */

/**
 * Proposito:
 * Prepara la vista de auditoria y sus controles de filtro.
 *
 * Uso:
 * Se usa cuando existe un contenedor de logs en la pagina de auditoria.
 *
 * Argumentos:
 * - contenedor {HTMLElement}: Elemento donde se renderizara la tabla de logs.
 *
 * Retorna:
 * - {void}: No retorna datos. Registra eventos y carga logs.
 *
 * Flujo:
 * 1. Busca el formulario de filtros y el boton de limpiar filtros.
 * 2. Registra el submit del formulario para actualizar logs.
 * 3. Registra el click del boton limpiar para reiniciar filtros.
 * 4. Realiza una carga inicial de logs.
 */
function prepararAuditoria(contenedor) {
    const formulario = document.querySelector('[data-filtros-auditoria]');
    const botonLimpiar = document.querySelector('[data-accion="limpiar-filtros-auditoria"]');

    if (formulario) {
        formulario.addEventListener('submit', (evento) => {
            evento.preventDefault();
            actualizarLogsAuditoria(contenedor, formulario);
        });
    }

    if (botonLimpiar && formulario) {
        botonLimpiar.addEventListener('click', () => {
            formulario.reset();
            actualizarLogsAuditoria(contenedor, formulario);
        });
    }

    actualizarLogsAuditoria(contenedor, formulario);
}


/**
 * Proposito:
 * Actualiza la tabla de auditoria usando los filtros actuales.
 *
 * Uso:
 * Se usa al cargar auditoria, al aplicar filtros y al limpiar filtros.
 *
 * Argumentos:
 * - contenedor {HTMLElement}: Elemento donde se mostraran los logs.
 * - formulario {HTMLFormElement|null}: Formulario desde donde se obtienen los filtros.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Actualiza la interfaz de auditoria.
 *
 * Flujo:
 * 1. Obtiene filtros desde el formulario si existe.
 * 2. Muestra una linea con los filtros aplicados.
 * 3. Consulta los logs al backend.
 * 4. Renderiza los logs en el contenedor.
 */
async function actualizarLogsAuditoria(contenedor, formulario) {
    const filtros = formulario ? obtenerFiltrosAuditoria(formulario) : {};
    mostrarFiltrosAplicadosAuditoria(filtros);
    const logs = await cargarLogs(filtros);
    mostrarLogs(contenedor, logs);
}

/**
 * Proposito:
 * Convierte los campos del formulario de auditoria en un objeto de filtros.
 *
 * Uso:
 * Se usa antes de consultar api/listar_logs.php.
 *
 * Argumentos:
 * - formulario {HTMLFormElement}: Formulario de filtros de auditoria.
 *
 * Retorna:
 * - {object}: Objeto con fecha, usuario, tipo de movimiento, modulo y tabla.
 *
 * Flujo:
 * 1. Crea un FormData con los campos del formulario.
 * 2. Lee cada filtro esperado.
 * 3. Si un campo no tiene valor, usa cadena vacia.
 * 4. Retorna el objeto listo para crear parametros de consulta.
 */
function obtenerFiltrosAuditoria(formulario) {
    const datos = new FormData(formulario);

    return {
        fecha_desde: datos.get('fecha_desde') || '',
        fecha_hasta: datos.get('fecha_hasta') || '',
        usuario: datos.get('usuario') || '',
        tipo_movimiento: datos.get('tipo_movimiento') || '',
        modulo: datos.get('modulo') || '',
        tabla_afectada: datos.get('tabla_afectada') || '',
    };
}


/**
 * Proposito:
 * Muestra en pantalla un resumen de los filtros de auditoria que estan activos.
 *
 * Uso:
 * Se usa cada vez que se actualiza la tabla de logs.
 *
 * Argumentos:
 * - filtros {object}: Objeto con los filtros seleccionados por el usuario.
 *
 * Retorna:
 * - {void}: No retorna datos. Cambia el texto del contenedor de filtros aplicados.
 *
 * Flujo:
 * 1. Busca el contenedor de filtros aplicados.
 * 2. Si no existe, termina la funcion.
 * 3. Define etiquetas legibles para cada filtro.
 * 4. Filtra valores vacios.
 * 5. Muestra los filtros activos o indica que no hay filtros.
 */
function mostrarFiltrosAplicadosAuditoria(filtros) {
    const contenedor = document.querySelector('[data-filtros-auditoria-aplicados]');

    if (!contenedor) {
        return;
    }

    const etiquetas = {
        fecha_desde: 'Desde',
        fecha_hasta: 'Hasta',
        usuario: 'Usuario',
        tipo_movimiento: 'Movimiento',
        modulo: 'Módulo',
        tabla_afectada: 'Tabla',
    };

    const valores = Object.entries(filtros)
        .filter(([, valor]) => String(valor || '').trim() !== '')
        .map(([clave, valor]) => `${etiquetas[clave] || clave}: ${formatearValorFiltroAuditoria(clave, valor)}`);

    contenedor.textContent = valores.length > 0
        ? `Filtros aplicados: ${valores.join(' · ')}`
        : 'Filtros aplicados: ninguno.';
}

/**
 * Proposito:
 * Formatea el valor de un filtro antes de mostrarlo al usuario.
 *
 * Uso:
 * Se usa al construir el resumen de filtros aplicados.
 *
 * Argumentos:
 * - clave {string}: Nombre interno del filtro.
 * - valor {string}: Valor seleccionado o escrito por el usuario.
 *
 * Retorna:
 * - {string}: Valor formateado para mostrar en pantalla.
 *
 * Flujo:
 * 1. Verifica si el filtro corresponde al tipo de movimiento.
 * 2. Si corresponde, convierte el codigo interno en texto legible.
 * 3. Si no requiere formato especial, retorna el valor original.
 */
function formatearValorFiltroAuditoria(clave, valor) {
    if (clave === 'tipo_movimiento') {
        return formatearMovimientoLog(valor);
    }

    return valor;
}

/**
 * Proposito:
 * Renderiza la tabla de logs de auditoria dentro del contenedor indicado.
 *
 * Uso:
 * Se usa despues de recibir los registros desde api/listar_logs.php.
 *
 * Argumentos:
 * - contenedor {HTMLElement}: Elemento donde se insertara la tabla.
 * - logs {Array}: Lista de registros de auditoria recibidos desde la API.
 *
 * Retorna:
 * - {void}: No retorna datos. Modifica el DOM con la tabla de logs.
 *
 * Flujo:
 * 1. Limpia el contenido anterior del contenedor.
 * 2. Si no hay logs, muestra un estado vacio.
 * 3. Crea la estructura de tabla, encabezado y cuerpo.
 * 4. Recorre cada log y crea una fila.
 * 5. Inserta la tabla final en el contenedor.
 */
function mostrarLogs(contenedor, logs) {
    contenedor.replaceChildren();

    if (!logs || logs.length === 0) {
        contenedor.appendChild(crearEstadoVacio('No hay registros que coincidan con los filtros seleccionados.'));
        return;
    }

    const envoltorio = crearElemento('div', 'logs-table-wrapper');
    const tabla = crearElemento('table', 'logs-table');
    const encabezado = document.createElement('thead');
    const filaEncabezado = document.createElement('tr');

    ['Fecha', 'Hora', 'Usuario', 'Movimiento', 'Módulo', 'Tabla', 'Registro', 'IP', 'Sistema', 'Navegador', 'Detalle'].forEach((titulo) => {
        filaEncabezado.appendChild(crearElemento('th', '', titulo));
    });

    encabezado.appendChild(filaEncabezado);

    const cuerpo = document.createElement('tbody');

    logs.forEach((log) => {
        const fila = document.createElement('tr');
        const usuario = log.usuario_nombre
            ? `${log.usuario_nombre} (${log.usuario_rol || 'sin rol'})`
            : 'Usuario eliminado o no disponible';
        const fechaEvento = String(log.fecha_evento || '').split(' ')[0] || 'Sin fecha';

        [
            fechaEvento,
            log.hora_evento || 'Sin hora',
            usuario,
            formatearMovimientoLog(log.tipo_movimiento),
            log.modulo || 'Sistema',
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

/**
 * Proposito:
 * Convierte el codigo interno de un movimiento en una etiqueta legible.
 *
 * Uso:
 * Se usa al mostrar logs y al mostrar filtros aplicados.
 *
 * Argumentos:
 * - tipoMovimiento {string}: Codigo interno del movimiento registrado.
 *
 * Retorna:
 * - {string}: Etiqueta legible del movimiento.
 *
 * Flujo:
 * 1. Define un mapa de codigos internos y textos visibles.
 * 2. Busca el codigo recibido dentro del mapa.
 * 3. Retorna la etiqueta encontrada o un texto alternativo.
 */
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
