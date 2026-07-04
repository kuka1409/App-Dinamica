// Renderizado del listado general de subtareas.

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
