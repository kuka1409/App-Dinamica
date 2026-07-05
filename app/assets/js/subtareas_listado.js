/**
 * Archivo: subtareas_listado.js
 *
 * Este archivo renderiza el listado general de subtareas. Convierte las
 * subtareas guardadas en el estado global en tarjetas HTML visibles.
 *
 * Funciones principales:
 * - Limpiar el contenedor del listado.
 * - Mostrar estado vacio si no hay subtareas.
 * - Crear tarjetas visuales con acciones de editar y eliminar.
 */

/**
 * Proposito:
 * Muestra el listado general de subtareas en formato de tarjetas.
 *
 * Uso:
 * Se usa despues de cargar estado.subtareas desde la API.
 *
 * Argumentos:
 * - contenedor {HTMLElement}: Elemento donde se insertaran las tarjetas.
 *
 * Retorna:
 * - {void}: No retorna datos. Renderiza contenido en el DOM.
 *
 * Flujo:
 * 1. Limpia el contenido actual del contenedor.
 * 2. Si no hay subtareas, muestra un estado vacio.
 * 3. Recorre cada subtarea del estado global.
 * 4. Crea una tarjeta con titulo, descripcion, estado y acciones.
 * 5. Inserta cada tarjeta en el contenedor.
 */
function mostrarSubtareas(contenedor) {
    contenedor.replaceChildren();

    if (estado.subtareas.length === 0) {
        contenedor.appendChild(crearEstadoVacio('Aún no tienes subtareas registradas.'));
        return;
    }

    estado.subtareas.forEach((subtarea) => {
        const tarjeta = crearElemento('article', 'registro-card');
        const encabezado = crearElemento('div', 'registro-card__header');
        const accionesEncabezado = crearElemento('div', 'registro-card__acciones');
        accionesEncabezado.append(
            crearElemento(
                'span',
                subtarea.completada ? 'estado-badge estado-badge--completada' : 'estado-badge estado-badge--pendiente',
                subtarea.completada ? 'Completada' : 'Pendiente'
            ),
            crearEnlaceEditarSubtarea(subtarea),
            crearEnlaceEliminarSubtarea(subtarea)
        );
        encabezado.append(
            crearElemento('h3', '', subtarea.titulo),
            accionesEncabezado
        );

        tarjeta.append(
            encabezado,
            crearElemento('p', '', subtarea.descripcion || 'Sin descripción.'),
            crearMeta([
                `Tarea: ${subtarea.titulo_tarea || 'Sin nombre'}`,
                `Actualizada ${formatearFecha(subtarea.fecha_actualizacion)}`,
            ])
        );

        contenedor.appendChild(tarjeta);
    });
}
