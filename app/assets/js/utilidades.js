// Funciones auxiliares reutilizadas por los módulos del frontend.

function formularioAObjeto(formulario) {
    const datos = {};
    const formData = new FormData(formulario);

    formData.forEach((valor, clave) => {
        datos[clave] = String(valor).trim();
    });

    return datos;
}

function obtenerParametroUrl(nombre) {
    const parametros = new URLSearchParams(window.location.search);
    return parametros.get(nombre) || '';
}

function crearOpcion(valor, texto) {
    const opcion = document.createElement('option');
    opcion.value = valor;
    opcion.textContent = texto;
    return opcion;
}

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

function crearMeta(items) {
    const contenedor = crearElemento('div', 'registro-card__meta');

    items.forEach((item) => {
        contenedor.appendChild(crearElemento('span', 'meta-pill', item));
    });

    return contenedor;
}

function crearEstadoVacio(texto) {
    return crearElemento('div', 'empty-state', texto);
}

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
