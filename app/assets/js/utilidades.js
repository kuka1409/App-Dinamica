/**
 * Archivo: utilidades.js
 *
 * Este archivo contiene funciones auxiliares reutilizables por varios modulos.
 * Agrupa tareas comunes como crear elementos HTML, leer parametros de URL,
 * convertir formularios en objetos y formatear fechas.
 *
 * Funciones principales:
 * - Convertir formularios a objetos.
 * - Crear elementos HTML.
 * - Crear mensajes de estado vacio.
 * - Formatear fechas para la interfaz.
 */

/**
 * Proposito:
 * Convierte los campos de un formulario HTML en un objeto JavaScript.
 *
 * Uso:
 * Se usa antes de enviar datos de formularios hacia la API.
 *
 * Argumentos:
 * - formulario {HTMLFormElement}: Formulario desde donde se leeran los campos.
 *
 * Retorna:
 * - {object}: Objeto con claves y valores limpios como texto.
 *
 * Flujo:
 * 1. Crea un objeto vacio.
 * 2. Crea FormData a partir del formulario.
 * 3. Recorre cada campo del formulario.
 * 4. Guarda cada valor como texto recortado.
 * 5. Retorna el objeto final.
 */
function formularioAObjeto(formulario) {
    const datos = {};
    const formData = new FormData(formulario);

    formData.forEach((valor, clave) => {
        datos[clave] = String(valor).trim();
    });

    return datos;
}

/**
 * Proposito:
 * Obtiene el valor de un parametro desde la URL actual.
 *
 * Uso:
 * Se usa para preseleccionar o enfocar tareas y subtareas desde enlaces.
 *
 * Argumentos:
 * - nombre {string}: Nombre del parametro que se quiere leer.
 *
 * Retorna:
 * - {string}: Valor del parametro o cadena vacia si no existe.
 *
 * Flujo:
 * 1. Crea URLSearchParams desde window.location.search.
 * 2. Busca el parametro solicitado.
 * 3. Retorna su valor o cadena vacia.
 */
function obtenerParametroUrl(nombre) {
    const parametros = new URLSearchParams(window.location.search);
    return parametros.get(nombre) || '';
}

/**
 * Proposito:
 * Crea una opcion HTML para un selector.
 *
 * Uso:
 * Se usa al rellenar selectores de tareas y subtareas.
 *
 * Argumentos:
 * - valor {string}: Valor interno de la opcion.
 * - texto {string}: Texto visible de la opcion.
 *
 * Retorna:
 * - {HTMLOptionElement}: Opcion lista para insertarse en un select.
 *
 * Flujo:
 * 1. Crea un elemento option.
 * 2. Asigna el value recibido.
 * 3. Asigna el texto visible.
 * 4. Retorna la opcion creada.
 */
function crearOpcion(valor, texto) {
    const opcion = document.createElement('option');
    opcion.value = valor;
    opcion.textContent = texto;
    return opcion;
}

/**
 * Proposito:
 * Crea un elemento HTML generico con clase y texto opcionales.
 *
 * Uso:
 * Se usa en casi todos los modulos para construir interfaz desde JS.
 *
 * Argumentos:
 * - etiqueta {string}: Nombre de la etiqueta HTML que se creara.
 * - clase {string}: Clase CSS opcional.
 * - texto {string}: Texto visible opcional.
 *
 * Retorna:
 * - {HTMLElement}: Elemento HTML configurado.
 *
 * Flujo:
 * 1. Crea el elemento usando document.createElement.
 * 2. Si recibe clase, la asigna como className.
 * 3. Si recibe texto, lo asigna como textContent.
 * 4. Retorna el elemento configurado.
 */
function crearElemento(etiqueta, clase = '', texto = '') {
    const elemento = document.createElement(etiqueta);

    if (clase) {
        elemento.className = clase;
    }

    if (texto !== '') {
        elemento.textContent = texto;
    }

    return elemento;
}

/**
 * Proposito:
 * Crea un bloque de metadatos visuales a partir de una lista de textos.
 *
 * Uso:
 * Se usa en tarjetas de subtareas para mostrar informacion adicional.
 *
 * Argumentos:
 * - items {Array}: Lista de textos que se convertiran en pildoras.
 *
 * Retorna:
 * - {HTMLElement}: Contenedor con las pildoras de metadatos.
 *
 * Flujo:
 * 1. Crea el contenedor principal de metadatos.
 * 2. Recorre cada item recibido.
 * 3. Crea un span por cada item.
 * 4. Inserta cada span dentro del contenedor.
 * 5. Retorna el contenedor completo.
 */
function crearMeta(items) {
    const contenedor = crearElemento('div', 'registro-card__meta');

    items.forEach((item) => {
        contenedor.appendChild(crearElemento('span', 'meta-pill', item));
    });

    return contenedor;
}

/**
 * Proposito:
 * Crea un bloque visual para indicar que no hay datos disponibles.
 *
 * Uso:
 * Se usa cuando un listado no tiene tareas, subtareas o logs.
 *
 * Argumentos:
 * - texto {string}: Mensaje que se mostrara en el estado vacio.
 *
 * Retorna:
 * - {HTMLElement}: Elemento div con clase empty-state.
 *
 * Flujo:
 * 1. Crea un div usando crearElemento.
 * 2. Asigna la clase empty-state.
 * 3. Asigna el texto recibido.
 * 4. Retorna el elemento creado.
 */
function crearEstadoVacio(texto) {
    return crearElemento('div', 'empty-state', texto);
}

/**
 * Proposito:
 * Convierte una fecha de base de datos en un formato legible para Chile.
 *
 * Uso:
 * Se usa al mostrar fechas de actualizacion en tareas y subtareas.
 *
 * Argumentos:
 * - fecha {string|null}: Fecha recibida desde la API o base de datos.
 *
 * Retorna:
 * - {string}: Fecha formateada o texto alternativo si no es valida.
 *
 * Flujo:
 * 1. Si no recibe fecha, retorna Sin fecha.
 * 2. Convierte el espacio entre fecha y hora en T para crear Date.
 * 3. Si la fecha no es valida, retorna el valor original.
 * 4. Formatea la fecha con configuracion es-CL.
 * 5. Retorna el texto formateado.
 */
function formatearFecha(fecha) {
    if (!fecha) {
        return 'Sin fecha';
    }

    const objetoFecha = new Date(fecha.replace(' ', 'T'));

    if (Number.isNaN(objetoFecha.getTime())) {
        return fecha;
    }

    return objetoFecha.toLocaleString('es-CL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}
