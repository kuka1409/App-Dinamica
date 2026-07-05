/**
 * Archivo: tareas_listado.js
 *
 * Este archivo renderiza y controla el listado principal de tareas. Construye
 * las tarjetas de tareas, sus subtareas desplegables, el progreso visual y los
 * eventos para expandir o cambiar estado de subtareas.
 *
 * Funciones principales:
 * - Mostrar tareas en tarjetas.
 * - Crear enlaces de accion.
 * - Renderizar subtareas desplegables.
 * - Calcular estados y progreso.
 * - Manejar clicks y cambios dentro del listado.
 */

/**
 * Proposito:
 * Muestra el listado principal de tareas dentro de un contenedor.
 *
 * Uso:
 * Se usa despues de cargar tareas o cuando se necesita refrescar la lista visual.
 *
 * Argumentos:
 * - contenedor {HTMLElement}: Elemento donde se insertaran las tarjetas de tareas.
 *
 * Retorna:
 * - {void}: No retorna datos. Renderiza tarjetas en el DOM.
 *
 * Flujo:
 * 1. Limpia el contenido actual del contenedor.
 * 2. Si no hay tareas, muestra un estado vacio.
 * 3. Crea un fragmento para insertar varias tarjetas de forma eficiente.
 * 4. Crea una tarjeta por cada tarea del estado global.
 * 5. Inserta el fragmento completo en el contenedor.
 */
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

/**
 * Proposito:
 * Crea la tarjeta visual completa de una tarea.
 *
 * Uso:
 * Se usa dentro de mostrarTareas para transformar cada objeto tarea en HTML.
 *
 * Argumentos:
 * - tarea {object}: Datos de la tarea recibidos desde el estado global.
 *
 * Retorna:
 * - {HTMLElement}: Tarjeta HTML lista para insertarse en la pagina.
 *
 * Flujo:
 * 1. Calcula el id, estado y si la tarea esta expandida.
 * 2. Crea la estructura principal de la tarjeta.
 * 3. Agrega titulo, descripcion, acciones y estado visual.
 * 4. Agrega metadatos, progreso y pie de tarjeta.
 * 5. Si la tarea esta expandida, agrega el desplegable de subtareas.
 * 6. Retorna la tarjeta terminada.
 */
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

/**
 * Proposito:
 * Crea el boton que permite mostrar u ocultar las subtareas de una tarea.
 *
 * Uso:
 * Se usa en el pie de cada tarjeta de tarea.
 *
 * Argumentos:
 * - tarea {object}: Tarea asociada al boton.
 * - estaExpandida {boolean}: Indica si la tarea ya muestra sus subtareas.
 *
 * Retorna:
 * - {HTMLButtonElement}: Boton configurado para alternar subtareas.
 *
 * Flujo:
 * 1. Crea el boton con el icono correspondiente.
 * 2. Define el tipo button para evitar envios de formulario.
 * 3. Guarda la accion y el id de tarea en dataset.
 * 4. Configura atributos accesibles segun el estado expandido.
 * 5. Retorna el boton listo para usar.
 */
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

/**
 * Proposito:
 * Crea un enlace para agregar una subtarea a una tarea especifica.
 *
 * Uso:
 * Se usa en el pie de cada tarjeta de tarea.
 *
 * Argumentos:
 * - tarea {object}: Tarea a la que se asociara la nueva subtarea.
 *
 * Retorna:
 * - {HTMLAnchorElement}: Enlace hacia el formulario de crear subtarea.
 *
 * Flujo:
 * 1. Crea el enlace con estilos de boton.
 * 2. Construye la URL con el id de la tarea.
 * 3. Agrega una etiqueta accesible.
 * 4. Retorna el enlace configurado.
 */
function crearEnlaceAgregarSubtarea(tarea) {
    const enlace = crearElemento('a', 'button button--soft button--small group-card__add-subtask', 'Agregar subtarea');
    enlace.href = `${rutaBase}paginas/subtareas_crear.php?id_tarea=${encodeURIComponent(tarea.id)}`;
    enlace.setAttribute('aria-label', `Agregar subtarea a ${tarea.titulo}`);
    return enlace;
}

/**
 * Proposito:
 * Crea el enlace de edicion de una tarea.
 *
 * Uso:
 * Se usa en el encabezado de cada tarjeta de tarea.
 *
 * Argumentos:
 * - tarea {object}: Tarea que se quiere editar.
 *
 * Retorna:
 * - {HTMLAnchorElement}: Enlace hacia el formulario de actualizar tarea.
 *
 * Flujo:
 * 1. Crea el elemento enlace.
 * 2. Construye la URL con el id de tarea.
 * 3. Agrega atributos accesibles y titulo.
 * 4. Inserta el icono de editar.
 * 5. Retorna el enlace configurado.
 */
function crearEnlaceEditarTarea(tarea) {
    const enlace = crearElemento('a', 'group-card__action-trigger group-card__edit-trigger group-card__edit-trigger--link');
    enlace.href = `${rutaBase}paginas/tareas_actualizar.php?id_tarea=${encodeURIComponent(tarea.id)}`;
    enlace.setAttribute('aria-label', `Editar tarea ${tarea.titulo}`);
    enlace.setAttribute('title', 'Editar tarea');
    enlace.appendChild(crearIconoEditar());
    return enlace;
}

/**
 * Proposito:
 * Crea el enlace de eliminacion de una tarea.
 *
 * Uso:
 * Se usa en el encabezado de cada tarjeta de tarea.
 *
 * Argumentos:
 * - tarea {object}: Tarea que se quiere eliminar.
 *
 * Retorna:
 * - {HTMLAnchorElement}: Enlace hacia el formulario de eliminar tarea.
 *
 * Flujo:
 * 1. Crea el elemento enlace.
 * 2. Construye la URL con el id de tarea.
 * 3. Agrega atributos accesibles y titulo.
 * 4. Inserta el icono de eliminar.
 * 5. Retorna el enlace configurado.
 */
function crearEnlaceEliminarTarea(tarea) {
    const enlace = crearElemento('a', 'group-card__action-trigger group-card__delete-trigger group-card__delete-trigger--link');
    enlace.href = `${rutaBase}paginas/tareas_eliminar.php?id_tarea=${encodeURIComponent(tarea.id)}`;
    enlace.setAttribute('aria-label', `Eliminar tarea ${tarea.titulo}`);
    enlace.setAttribute('title', 'Eliminar tarea');
    enlace.appendChild(crearIconoEliminar());
    return enlace;
}

/**
 * Proposito:
 * Crea el enlace de edicion de una subtarea.
 *
 * Uso:
 * Se usa en subtareas desplegadas y en el listado general de subtareas.
 *
 * Argumentos:
 * - subtarea {object}: Subtarea que se quiere editar.
 *
 * Retorna:
 * - {HTMLAnchorElement}: Enlace hacia el formulario de actualizar subtarea.
 *
 * Flujo:
 * 1. Crea el elemento enlace.
 * 2. Construye la URL con id de subtarea e id de tarea.
 * 3. Agrega atributos accesibles y titulo.
 * 4. Inserta el icono de editar.
 * 5. Retorna el enlace configurado.
 */
function crearEnlaceEditarSubtarea(subtarea) {
    const enlace = crearElemento('a', 'group-card-task__action-trigger group-card-task__edit-trigger');
    enlace.href = `${rutaBase}paginas/subtareas_actualizar.php?id_subtarea=${encodeURIComponent(subtarea.id)}&id_tarea=${encodeURIComponent(subtarea.id_tarea)}`;
    enlace.setAttribute('aria-label', `Editar subtarea ${subtarea.titulo}`);
    enlace.setAttribute('title', 'Editar subtarea');
    enlace.appendChild(crearIconoEditar());
    return enlace;
}

/**
 * Proposito:
 * Crea el enlace de eliminacion de una subtarea.
 *
 * Uso:
 * Se usa en subtareas desplegadas y en el listado general de subtareas.
 *
 * Argumentos:
 * - subtarea {object}: Subtarea que se quiere eliminar.
 *
 * Retorna:
 * - {HTMLAnchorElement}: Enlace hacia el formulario de eliminar subtarea.
 *
 * Flujo:
 * 1. Crea el elemento enlace.
 * 2. Construye la URL con id de subtarea e id de tarea.
 * 3. Agrega atributos accesibles y titulo.
 * 4. Inserta el icono de eliminar.
 * 5. Retorna el enlace configurado.
 */
function crearEnlaceEliminarSubtarea(subtarea) {
    const enlace = crearElemento('a', 'group-card-task__action-trigger group-card-task__delete-trigger');
    enlace.href = `${rutaBase}paginas/subtareas_eliminar.php?id_subtarea=${encodeURIComponent(subtarea.id)}&id_tarea=${encodeURIComponent(subtarea.id_tarea)}`;
    enlace.setAttribute('aria-label', `Eliminar subtarea ${subtarea.titulo}`);
    enlace.setAttribute('title', 'Eliminar subtarea');
    enlace.appendChild(crearIconoEliminar());
    return enlace;
}

/**
 * Proposito:
 * Crea el icono visual usado para acciones de edicion.
 *
 * Uso:
 * Se usa dentro de enlaces de editar tareas y subtareas.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {HTMLElement}: Elemento span con las clases del icono de editar.
 *
 * Flujo:
 * 1. Crea un span con clases de icono.
 * 2. Marca el icono como decorativo con aria-hidden.
 * 3. Retorna el elemento.
 */
function crearIconoEditar() {
    const icono = crearElemento('span', 'action-icon action-icon--editar');
    icono.setAttribute('aria-hidden', 'true');
    return icono;
}

/**
 * Proposito:
 * Crea el icono visual usado para acciones de eliminacion.
 *
 * Uso:
 * Se usa dentro de enlaces de eliminar tareas y subtareas.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {HTMLElement}: Elemento span con las clases del icono de eliminar.
 *
 * Flujo:
 * 1. Crea un span con clases de icono.
 * 2. Marca el icono como decorativo con aria-hidden.
 * 3. Retorna el elemento.
 */
function crearIconoEliminar() {
    const icono = crearElemento('span', 'action-icon action-icon--eliminar');
    icono.setAttribute('aria-hidden', 'true');
    return icono;
}

/**
 * Proposito:
 * Expande automaticamente una tarea si su id viene en la URL.
 *
 * Uso:
 * Se usa al cargar el listado de tareas despues de venir desde otra operacion.
 *
 * Argumentos:
 * - No recibe argumentos.
 *
 * Retorna:
 * - {void}: No retorna datos. Modifica estado.idsTareasExpandidas si corresponde.
 *
 * Flujo:
 * 1. Lee el parametro id_tarea desde la URL.
 * 2. Valida que sea un numero positivo.
 * 3. Verifica que exista una tarea con ese id.
 * 4. Si existe, agrega el id al conjunto de tareas expandidas.
 */
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

/**
 * Proposito:
 * Resalta y desplaza hacia una tarea indicada en la URL.
 *
 * Uso:
 * Se usa despues de renderizar tareas para ubicar visualmente una tarea especifica.
 *
 * Argumentos:
 * - contenedor {HTMLElement}: Contenedor donde estan renderizadas las tarjetas.
 *
 * Retorna:
 * - {void}: No retorna datos. Aplica una clase temporal y hace scroll.
 *
 * Flujo:
 * 1. Lee el parametro id_tarea desde la URL.
 * 2. Valida que sea un numero positivo.
 * 3. Busca la tarjeta correspondiente en el contenedor.
 * 4. Si la encuentra, agrega clase de enfoque y hace scroll.
 * 5. Despues de un tiempo, elimina la clase de enfoque.
 */
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

/**
 * Proposito:
 * Crea la seccion desplegable con las subtareas de una tarea.
 *
 * Uso:
 * Se usa cuando una tarjeta de tarea esta expandida.
 *
 * Argumentos:
 * - tarea {object}: Tarea que contiene o referencia sus subtareas.
 *
 * Retorna:
 * - {HTMLElement}: Seccion HTML con el listado de subtareas.
 *
 * Flujo:
 * 1. Crea la seccion y su encabezado.
 * 2. Obtiene la lista de subtareas de la tarea.
 * 3. Si no hay subtareas, muestra un mensaje vacio.
 * 4. Ordena subtareas pendientes antes que completadas.
 * 5. Crea un elemento visual por cada subtarea.
 * 6. Retorna la seccion terminada.
 */
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

/**
 * Proposito:
 * Crea el elemento visual de una subtarea dentro del desplegable de una tarea.
 *
 * Uso:
 * Se usa al construir el desplegable de subtareas.
 *
 * Argumentos:
 * - subtarea {object}: Datos de la subtarea que se renderizara.
 *
 * Retorna:
 * - {HTMLElement}: Elemento li listo para insertarse en la lista.
 *
 * Flujo:
 * 1. Crea el elemento li con clase segun su estado.
 * 2. Crea el checkbox para cambiar completada o pendiente.
 * 3. Agrega titulo y descripcion si existe.
 * 4. Crea la etiqueta visual de estado.
 * 5. Agrega enlaces de editar y eliminar.
 * 6. Retorna el elemento terminado.
 */
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

/**
 * Proposito:
 * Crea la insignia visual del estado de una tarea.
 *
 * Uso:
 * Se usa en el encabezado de cada tarjeta de tarea.
 *
 * Argumentos:
 * - estadoTarea {string}: Codigo interno del estado de la tarea.
 *
 * Retorna:
 * - {HTMLElement}: Elemento span con texto y clase de estado.
 *
 * Flujo:
 * 1. Obtiene la clase visual segun el estado.
 * 2. Obtiene la etiqueta visible segun el estado.
 * 3. Crea y retorna el span de insignia.
 */
function crearInsigniaEstadoTarea(estadoTarea) {
    return crearElemento(
        'span',
        `status-badge status-badge--${obtenerClaseEstadoTarea(estadoTarea)}`,
        obtenerEtiquetaEstadoTarea(estadoTarea)
    );
}

/**
 * Proposito:
 * Crea una pildora de metadato con etiqueta y valor destacado.
 *
 * Uso:
 * Se usa para mostrar estado, subtareas y completadas dentro de la tarjeta.
 *
 * Argumentos:
 * - etiqueta {string}: Texto descriptivo del dato.
 * - valor {string}: Valor visible del dato.
 *
 * Retorna:
 * - {HTMLElement}: Elemento HTML con etiqueta y valor.
 *
 * Flujo:
 * 1. Crea el contenedor de la pildora.
 * 2. Agrega un span para la etiqueta.
 * 3. Agrega un strong para el valor.
 * 4. Retorna el contenedor armado.
 */
function crearPildoraMetaDetallada(etiqueta, valor) {
    const pildora = crearElemento('div', 'meta-pill');
    pildora.append(
        crearElemento('span', 'meta-pill__label', etiqueta),
        crearElemento('strong', 'meta-pill__value', valor)
    );
    return pildora;
}

/**
 * Proposito:
 * Crea la seccion visual de progreso de una tarea.
 *
 * Uso:
 * Se usa dentro de cada tarjeta de tarea.
 *
 * Argumentos:
 * - tarea {object}: Tarea desde la que se calculara el progreso.
 *
 * Retorna:
 * - {HTMLElement}: Bloque HTML con porcentaje y barra de progreso.
 *
 * Flujo:
 * 1. Calcula el porcentaje de progreso.
 * 2. Crea la fila de texto con etiqueta y valor.
 * 3. Crea la pista y el relleno de la barra.
 * 4. Asigna el ancho del relleno segun el porcentaje.
 * 5. Retorna el contenedor de progreso.
 */
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

/**
 * Proposito:
 * Maneja clicks delegados dentro del listado de tareas.
 *
 * Uso:
 * Se usa como listener del contenedor de tareas para detectar botones internos.
 *
 * Argumentos:
 * - evento {MouseEvent}: Evento click generado dentro del listado.
 *
 * Retorna:
 * - {void}: No retorna datos. Ejecuta acciones segun el boton clickeado.
 *
 * Flujo:
 * 1. Busca el boton mas cercano con accion de tarjeta.
 * 2. Si no hay boton, termina la funcion.
 * 3. Si la accion es alternar subtareas, llama a alternarSubtareasTarea.
 */
function manejarClicListadoTareas(evento) {
    const boton = evento.target.closest('button[data-accion-tarjeta]');

    if (!boton) {
        return;
    }

    if (boton.dataset.accionTarjeta === 'alternar-subtareas') {
        alternarSubtareasTarea(Number(boton.dataset.idTarea), evento.currentTarget);
    }
}

/**
 * Proposito:
 * Maneja cambios delegados dentro del listado de tareas.
 *
 * Uso:
 * Se usa para detectar cambios en checkboxes de subtareas.
 *
 * Argumentos:
 * - evento {Event}: Evento change generado dentro del listado.
 *
 * Retorna:
 * - {void}: No retorna datos. Lanza la actualizacion del estado de la subtarea.
 *
 * Flujo:
 * 1. Obtiene el elemento que disparo el evento.
 * 2. Verifica que sea un input y que tenga la accion correcta.
 * 3. Llama a alternarEstadoSubtarea con la casilla y el contenedor.
 */
function manejarCambioListadoTareas(evento) {
    const casilla = evento.target;

    if (!(casilla instanceof HTMLInputElement) || casilla.dataset.accionSubtarea !== 'alternar-estado') {
        return;
    }

    alternarEstadoSubtarea(casilla, evento.currentTarget);
}

/**
 * Proposito:
 * Alterna si una tarea muestra u oculta sus subtareas.
 *
 * Uso:
 * Se usa cuando el usuario presiona el boton de desplegar subtareas.
 *
 * Argumentos:
 * - idTarea {number}: Identificador de la tarea que se alternara.
 * - contenedor {HTMLElement}: Contenedor donde se renderiza el listado.
 *
 * Retorna:
 * - {void}: No retorna datos. Actualiza estado y vuelve a renderizar.
 *
 * Flujo:
 * 1. Verifica si el id ya esta en tareas expandidas.
 * 2. Si existe, lo elimina para cerrar el desplegable.
 * 3. Si no existe, lo agrega para abrir el desplegable.
 * 4. Vuelve a renderizar el listado de tareas.
 */
function alternarSubtareasTarea(idTarea, contenedor) {
    if (estado.idsTareasExpandidas.has(idTarea)) {
        estado.idsTareasExpandidas.delete(idTarea);
    } else {
        estado.idsTareasExpandidas.add(idTarea);
    }

    mostrarTareas(contenedor);
}

/**
 * Proposito:
 * Cambia el estado completada/pendiente de una subtarea desde el checkbox.
 *
 * Uso:
 * Se usa cuando el usuario marca o desmarca una subtarea dentro de una tarjeta.
 *
 * Argumentos:
 * - casilla {HTMLInputElement}: Checkbox que contiene el id de la subtarea.
 * - contenedor {HTMLElement}: Contenedor del listado de tareas.
 *
 * Retorna:
 * - {Promise<void>}: No retorna datos. Actualiza backend, estado e interfaz.
 *
 * Flujo:
 * 1. Guarda el nuevo valor completada.
 * 2. Deshabilita la casilla mientras se procesa.
 * 3. Envia el cambio al endpoint correspondiente.
 * 4. Recarga tareas y vuelve a mostrar el listado.
 * 5. Si ocurre un error, revierte el checkbox y muestra mensaje.
 * 6. Finalmente vuelve a habilitar la casilla.
 */
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

/**
 * Proposito:
 * Calcula el porcentaje de avance de una tarea segun sus subtareas.
 *
 * Uso:
 * Se usa para mostrar la barra de progreso de cada tarjeta.
 *
 * Argumentos:
 * - tarea {object}: Tarea con total de subtareas y subtareas completadas.
 *
 * Retorna:
 * - {number}: Porcentaje entero entre 0 y 100.
 *
 * Flujo:
 * 1. Lee el total de subtareas.
 * 2. Si no hay subtareas, retorna 0.
 * 3. Divide completadas por total y multiplica por 100.
 * 4. Redondea el resultado y lo retorna.
 */
function calcularProgresoTarea(tarea) {
    const totalSubtareas = Number(tarea.total_subtareas || 0);

    if (totalSubtareas <= 0) {
        return 0;
    }

    return Math.round((Number(tarea.subtareas_completadas || 0) / totalSubtareas) * 100);
}

/**
 * Proposito:
 * Determina el estado logico de una tarea.
 *
 * Uso:
 * Se usa para clases visuales, etiquetas y notas de la tarjeta.
 *
 * Argumentos:
 * - tarea {object}: Tarea que se analizara.
 *
 * Retorna:
 * - {string}: Codigo de estado como vacio, en_progreso o completado.
 *
 * Flujo:
 * 1. Si la tarea ya trae un estado, lo retorna.
 * 2. Calcula total y completadas desde la tarea.
 * 3. Si no hay subtareas, retorna vacio.
 * 4. Si todas estan completas, retorna completado.
 * 5. En cualquier otro caso, retorna en_progreso.
 */
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

/**
 * Proposito:
 * Convierte un codigo de estado de tarea en texto visible.
 *
 * Uso:
 * Se usa para insignias y metadatos de tarjetas.
 *
 * Argumentos:
 * - estadoTarea {string}: Codigo interno del estado.
 *
 * Retorna:
 * - {string}: Etiqueta visible del estado.
 *
 * Flujo:
 * 1. Define el mapa de estados y etiquetas.
 * 2. Busca la etiqueta del estado recibido.
 * 3. Retorna la etiqueta encontrada o Sin estado.
 */
function obtenerEtiquetaEstadoTarea(estadoTarea) {
    const etiquetas = {
        vacio: 'Sin subtareas',
        en_progreso: 'En progreso',
        completado: 'Completada',
    };

    return etiquetas[estadoTarea] || 'Sin estado';
}

/**
 * Proposito:
 * Convierte un estado de tarea en clase CSS para la tarjeta.
 *
 * Uso:
 * Se usa para aplicar estilos segun el estado de la tarea.
 *
 * Argumentos:
 * - estadoTarea {string}: Codigo interno del estado.
 *
 * Retorna:
 * - {string}: Sufijo de clase CSS asociado al estado.
 *
 * Flujo:
 * 1. Define el mapa de estados y clases.
 * 2. Busca la clase del estado recibido.
 * 3. Retorna la clase encontrada o empty.
 */
function obtenerClaseEstadoTarea(estadoTarea) {
    const clases = {
        vacio: 'empty',
        en_progreso: 'in_progress',
        completado: 'completed',
    };

    return clases[estadoTarea] || 'empty';
}

/**
 * Proposito:
 * Genera un resumen textual sobre las subtareas de una tarea.
 *
 * Uso:
 * Se usa en el encabezado del desplegable de subtareas.
 *
 * Argumentos:
 * - tarea {object}: Tarea con conteos de subtareas.
 *
 * Retorna:
 * - {string}: Texto resumen para mostrar en pantalla.
 *
 * Flujo:
 * 1. Lee total de subtareas y pendientes.
 * 2. Si no hay subtareas, retorna mensaje de ausencia.
 * 3. Si hay pendientes, retorna cuantas faltan por completar.
 * 4. Si no hay pendientes, retorna que todas estan completas.
 */
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

/**
 * Proposito:
 * Genera la nota inferior de una tarjeta de tarea.
 *
 * Uso:
 * Se usa en el pie de cada tarjeta junto a la fecha de actualizacion.
 *
 * Argumentos:
 * - tarea {object}: Tarea con conteos de subtareas.
 * - estadoTarea {string}: Estado calculado de la tarea.
 *
 * Retorna:
 * - {string}: Texto corto para el pie de la tarjeta.
 *
 * Flujo:
 * 1. Lee la cantidad de subtareas pendientes.
 * 2. Si hay pendientes, retorna ese conteo.
 * 3. Si la tarea esta completada, retorna mensaje de completado.
 * 4. Si no hay subtareas, retorna mensaje inicial.
 */
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
