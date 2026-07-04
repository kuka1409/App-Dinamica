// Renderizado y comportamiento del listado de tareas.

function mostrarTareas(contenedor) {
    contenedor.replaceChildren();

    if (estado.tareas.length === 0) {
        contenedor.appendChild(crearEstadoVacio('Aún no tienes tareas registradas.'));
        return;
    }

    const fragmento = document.createDocumentFragment();

    estado.tareas.forEach((tarea) => {
        fragmento.appendChild(crearTarjetaTarea(tarea));
    });

    contenedor.appendChild(fragmento);
}

function crearTarjetaTarea(tarea) {
    const idTarea = Number(tarea.id);
    const estadoTarea = obtenerEstadoTarea(tarea);
    const estaExpandida = estado.idsTareasExpandidas.has(idTarea);
    const tarjeta = crearElemento('article', `group-card group-card--${obtenerClaseEstadoTarea(estadoTarea)}`);
    tarjeta.dataset.idTarea = String(idTarea);

    const encabezado = crearElemento('div', 'group-card__header');
    const bloqueTitulo = crearElemento('div', 'group-card__title-block');
    const filaTitulo = crearElemento('div', 'group-card__title-row');
    filaTitulo.append(
        crearElemento('h3', 'group-card__title', tarea.titulo),
        crearEnlaceEditarTarea(tarea),
        crearEnlaceEliminarTarea(tarea)
    );

    bloqueTitulo.append(filaTitulo);

    const descripcionTarea = String(tarea.descripcion || '').trim();
    if (descripcionTarea) {
        bloqueTitulo.appendChild(crearElemento('p', 'group-card__description', descripcionTarea));
    }

    encabezado.append(bloqueTitulo, crearInsigniaEstadoTarea(estadoTarea));

    const metadatos = crearElemento('div', 'group-card__meta');
    metadatos.append(
        crearPildoraMetaDetallada('Estado', obtenerEtiquetaEstadoTarea(estadoTarea)),
        crearPildoraMetaDetallada('Subtareas', String(tarea.total_subtareas || 0)),
        crearPildoraMetaDetallada('Completadas', `${tarea.subtareas_completadas || 0}/${tarea.total_subtareas || 0}`)
    );

    const pie = crearElemento('div', 'group-card__footer');
    const metaPie = crearElemento('div', 'group-card__footer-meta');
    metaPie.append(
        crearElemento('span', 'group-card__footer-note', obtenerNotaPieTarea(tarea, estadoTarea)),
        crearElemento('span', 'group-card__footer-note', `Actualizada ${formatearFecha(tarea.fecha_actualizacion)}`)
    );

    const controlesPie = crearElemento('div', 'group-card__footer-controls');
    controlesPie.append(
        crearEnlaceAgregarSubtarea(tarea),
        crearBotonAlternarSubtareas(tarea, estaExpandida)
    );
    pie.append(metaPie, controlesPie);

    tarjeta.append(encabezado, metadatos, crearSeccionProgresoTarea(tarea), pie);

    if (estaExpandida) {
        tarjeta.appendChild(crearDesplegableSubtareas(tarea));
    }

    return tarjeta;
}

function crearBotonAlternarSubtareas(tarea, estaExpandida) {
    const boton = crearElemento('button', 'group-card__toggle-trigger', estaExpandida ? '▴' : '▾');
    boton.type = 'button';
    boton.dataset.accionTarjeta = 'alternar-subtareas';
    boton.dataset.idTarea = String(tarea.id);
    boton.setAttribute('aria-label', estaExpandida ? 'Ocultar subtareas' : 'Ver subtareas');
    boton.setAttribute('title', estaExpandida ? 'Ocultar subtareas' : 'Ver subtareas');
    boton.setAttribute('aria-expanded', estaExpandida ? 'true' : 'false');
    return boton;
}

function crearEnlaceAgregarSubtarea(tarea) {
    const enlace = crearElemento('a', 'button button--soft button--small group-card__add-subtask', 'Agregar subtarea');
    enlace.href = `${rutaBase}paginas/subtareas_crear.php?id_tarea=${encodeURIComponent(tarea.id)}`;
    enlace.setAttribute('aria-label', `Agregar subtarea a ${tarea.titulo}`);
    return enlace;
}

function crearEnlaceEditarTarea(tarea) {
    const enlace = crearElemento('a', 'group-card__action-trigger group-card__edit-trigger group-card__edit-trigger--link');
    enlace.href = `${rutaBase}paginas/tareas_actualizar.php?id_tarea=${encodeURIComponent(tarea.id)}`;
    enlace.setAttribute('aria-label', `Editar tarea ${tarea.titulo}`);
    enlace.setAttribute('title', 'Editar tarea');
    enlace.appendChild(crearIconoEditar());
    return enlace;
}

function crearEnlaceEliminarTarea(tarea) {
    const enlace = crearElemento('a', 'group-card__action-trigger group-card__delete-trigger group-card__delete-trigger--link');
    enlace.href = `${rutaBase}paginas/tareas_eliminar.php?id_tarea=${encodeURIComponent(tarea.id)}`;
    enlace.setAttribute('aria-label', `Eliminar tarea ${tarea.titulo}`);
    enlace.setAttribute('title', 'Eliminar tarea');
    enlace.appendChild(crearIconoEliminar());
    return enlace;
}

function crearEnlaceEditarSubtarea(subtarea) {
    const enlace = crearElemento('a', 'group-card-task__action-trigger group-card-task__edit-trigger');
    enlace.href = `${rutaBase}paginas/subtareas_actualizar.php?id_subtarea=${encodeURIComponent(subtarea.id)}&id_tarea=${encodeURIComponent(subtarea.id_tarea)}`;
    enlace.setAttribute('aria-label', `Editar subtarea ${subtarea.titulo}`);
    enlace.setAttribute('title', 'Editar subtarea');
    enlace.appendChild(crearIconoEditar());
    return enlace;
}

function crearEnlaceEliminarSubtarea(subtarea) {
    const enlace = crearElemento('a', 'group-card-task__action-trigger group-card-task__delete-trigger');
    enlace.href = `${rutaBase}paginas/subtareas_eliminar.php?id_subtarea=${encodeURIComponent(subtarea.id)}&id_tarea=${encodeURIComponent(subtarea.id_tarea)}`;
    enlace.setAttribute('aria-label', `Eliminar subtarea ${subtarea.titulo}`);
    enlace.setAttribute('title', 'Eliminar subtarea');
    enlace.appendChild(crearIconoEliminar());
    return enlace;
}

function crearIconoEditar() {
    const icono = crearElemento('span', 'action-icon', '✎');
    icono.setAttribute('aria-hidden', 'true');
    return icono;
}

function crearIconoEliminar() {
    const icono = crearElemento('span', 'action-icon', '×');
    icono.setAttribute('aria-hidden', 'true');
    return icono;
}

function expandirTareaDesdeUrl() {
    const idTarea = Number(obtenerParametroUrl('id_tarea'));

    if (!Number.isInteger(idTarea) || idTarea <= 0) {
        return;
    }

    const existeTarea = estado.tareas.some((tarea) => Number(tarea.id) === idTarea);

    if (existeTarea) {
        estado.idsTareasExpandidas.add(idTarea);
    }
}

function enfocarTareaDesdeUrl(contenedor) {
    const idTarea = Number(obtenerParametroUrl('id_tarea'));

    if (!Number.isInteger(idTarea) || idTarea <= 0) {
        return;
    }

    const tarjeta = Array.from(contenedor.querySelectorAll('[data-id-tarea]')).find((elemento) => {
        return Number(elemento.dataset.idTarea) === idTarea;
    });

    if (!tarjeta) {
        return;
    }

    tarjeta.classList.add('group-card--focus');
    tarjeta.scrollIntoView({ behavior: 'smooth', block: 'center' });

    window.setTimeout(() => {
        tarjeta.classList.remove('group-card--focus');
    }, 2600);
}

function crearDesplegableSubtareas(tarea) {
    const seccion = crearElemento('section', 'group-card__dropdown');
    const subtareas = Array.isArray(tarea.subtareas) ? tarea.subtareas : [];
    const encabezado = crearElemento('div', 'group-card__dropdown-header');

    encabezado.append(
        crearElemento('h4', 'group-card__dropdown-title', 'Subtareas'),
        crearElemento('span', 'group-card__dropdown-caption', obtenerResumenSubtareas(tarea))
    );
    seccion.appendChild(encabezado);

    if (subtareas.length === 0) {
        seccion.appendChild(
            crearElemento(
                'div',
                'group-card__dropdown-empty',
                'Esta tarea aún no tiene subtareas registradas.'
            )
        );
        return seccion;
    }

    const lista = crearElemento('ul', 'group-card__dropdown-list');
    const subtareasOrdenadas = [...subtareas].sort((subtareaIzquierda, subtareaDerecha) => {
        const diferenciaCompletadas = Number(subtareaIzquierda.completada) - Number(subtareaDerecha.completada);

        if (diferenciaCompletadas !== 0) {
            return diferenciaCompletadas;
        }

        return Number(subtareaIzquierda.posicion || 0) - Number(subtareaDerecha.posicion || 0);
    });

    subtareasOrdenadas.forEach((subtarea) => {
        lista.appendChild(crearElementoSubtareaDesplegable(subtarea));
    });
    seccion.appendChild(lista);

    return seccion;
}

function crearElementoSubtareaDesplegable(subtarea) {
    const elemento = crearElemento(
        'li',
        subtarea.completada
            ? 'group-card-task group-card-task--completed'
            : 'group-card-task'
    );
    elemento.dataset.idSubtarea = String(subtarea.id);

    const alternador = crearElemento('label', 'group-card-task__toggle');
    const casilla = document.createElement('input');
    casilla.type = 'checkbox';
    casilla.checked = Boolean(subtarea.completada);
    casilla.dataset.accionSubtarea = 'alternar-estado';
    casilla.dataset.idSubtarea = String(subtarea.id);

    const texto = crearElemento('div', 'group-card-task__copy');
    texto.appendChild(crearElemento('span', 'group-card-task__title', subtarea.titulo));

    if (subtarea.descripcion) {
        texto.appendChild(crearElemento('span', 'group-card-task__description', subtarea.descripcion));
    }

    alternador.append(casilla, texto);

    const estadoVisual = crearElemento(
        'span',
        subtarea.completada
            ? 'group-card-task__status group-card-task__status--completed'
            : 'group-card-task__status group-card-task__status--pending',
        subtarea.completada ? 'Completada' : 'Pendiente'
    );

    const acciones = crearElemento('div', 'group-card-task__actions');
    acciones.append(estadoVisual, crearEnlaceEditarSubtarea(subtarea), crearEnlaceEliminarSubtarea(subtarea));

    elemento.append(alternador, acciones);
    return elemento;
}

function crearInsigniaEstadoTarea(estadoTarea) {
    return crearElemento(
        'span',
        `status-badge status-badge--${obtenerClaseEstadoTarea(estadoTarea)}`,
        obtenerEtiquetaEstadoTarea(estadoTarea)
    );
}

function crearPildoraMetaDetallada(etiqueta, valor) {
    const pildora = crearElemento('div', 'meta-pill');
    pildora.append(
        crearElemento('span', 'meta-pill__label', etiqueta),
        crearElemento('strong', 'meta-pill__value', valor)
    );
    return pildora;
}

function crearSeccionProgresoTarea(tarea) {
    const contenedor = crearElemento('div', 'progress-block');
    const filaTexto = crearElemento('div', 'progress-block__text');
    const porcentaje = calcularProgresoTarea(tarea);

    filaTexto.append(
        crearElemento('span', 'progress-block__label', 'Progreso de la tarea'),
        crearElemento('strong', 'progress-block__value', `${porcentaje}%`)
    );

    const pista = crearElemento('div', 'progress-track');
    const relleno = crearElemento('div', 'progress-fill');
    relleno.style.width = `${porcentaje}%`;
    pista.appendChild(relleno);
    contenedor.append(filaTexto, pista);

    return contenedor;
}

function manejarClicListadoTareas(evento) {
    const boton = evento.target.closest('button[data-accion-tarjeta]');

    if (!boton) {
        return;
    }

    if (boton.dataset.accionTarjeta === 'alternar-subtareas') {
        alternarSubtareasTarea(Number(boton.dataset.idTarea), evento.currentTarget);
    }
}

function manejarCambioListadoTareas(evento) {
    const casilla = evento.target;

    if (!(casilla instanceof HTMLInputElement) || casilla.dataset.accionSubtarea !== 'alternar-estado') {
        return;
    }

    alternarEstadoSubtarea(casilla, evento.currentTarget);
}

function alternarSubtareasTarea(idTarea, contenedor) {
    if (estado.idsTareasExpandidas.has(idTarea)) {
        estado.idsTareasExpandidas.delete(idTarea);
    } else {
        estado.idsTareasExpandidas.add(idTarea);
    }

    mostrarTareas(contenedor);
}

async function alternarEstadoSubtarea(casilla, contenedor) {
    const completada = casilla.checked;
    const mensaje = obtenerMensaje('listar-tareas');
    casilla.disabled = true;

    try {
        const respuesta = await peticionApi('api/cambiar_estado_subtarea.php', 'POST', {
            id_subtarea: Number(casilla.dataset.idSubtarea),
            completada,
        });

        await cargarTareas();
        mostrarTareas(contenedor);
        mostrarMensaje(mensaje, respuesta.mensaje || 'Estado actualizado correctamente.', 'success');
    } catch (error) {
        casilla.checked = !completada;
        mostrarMensaje(mensaje, error.message, 'error');
    } finally {
        casilla.disabled = false;
    }
}

function calcularProgresoTarea(tarea) {
    const totalSubtareas = Number(tarea.total_subtareas || 0);

    if (totalSubtareas <= 0) {
        return 0;
    }

    return Math.round((Number(tarea.subtareas_completadas || 0) / totalSubtareas) * 100);
}

function obtenerEstadoTarea(tarea) {
    if (tarea.estado) {
        return tarea.estado;
    }

    const totalSubtareas = Number(tarea.total_subtareas || 0);
    const subtareasCompletadas = Number(tarea.subtareas_completadas || 0);

    if (totalSubtareas <= 0) {
        return 'vacio';
    }

    if (subtareasCompletadas >= totalSubtareas) {
        return 'completado';
    }

    return 'en_progreso';
}

function obtenerEtiquetaEstadoTarea(estadoTarea) {
    const etiquetas = {
        vacio: 'Sin subtareas',
        en_progreso: 'En progreso',
        completado: 'Completada',
    };

    return etiquetas[estadoTarea] || 'Sin estado';
}

function obtenerClaseEstadoTarea(estadoTarea) {
    const clases = {
        vacio: 'empty',
        en_progreso: 'in_progress',
        completado: 'completed',
    };

    return clases[estadoTarea] || 'empty';
}

function obtenerResumenSubtareas(tarea) {
    const totalSubtareas = Number(tarea.total_subtareas || 0);
    const subtareasPendientes = Number(tarea.subtareas_pendientes || 0);

    if (totalSubtareas <= 0) {
        return 'Sin subtareas registradas';
    }

    if (subtareasPendientes > 0) {
        return `${subtareasPendientes} pendientes por completar`;
    }

    return 'Todas las subtareas están completas';
}

function obtenerNotaPieTarea(tarea, estadoTarea) {
    const subtareasPendientes = Number(tarea.subtareas_pendientes || 0);

    if (subtareasPendientes > 0) {
        return `${subtareasPendientes} subtareas pendientes`;
    }

    if (estadoTarea === 'completado') {
        return 'Todas las subtareas están completas';
    }

    return 'Aún no hay subtareas registradas';
}
