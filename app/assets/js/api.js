// Comunicación centralizada con los endpoints JSON.

async function peticionApi(url, metodo = 'GET', datos = null) {
    const urlFinal = url.startsWith('http') || url.startsWith('/') ? url : `${rutaBase}${url}`;
    const opciones = {
        method: metodo,
        headers: {},
    };

    if (datos !== null) {
        opciones.headers['Content-Type'] = 'application/json';
        opciones.body = JSON.stringify(datos);
    }

    const respuesta = await fetch(urlFinal, opciones);
    const contenido = await respuesta.json().catch(() => ({}));

    if (!respuesta.ok || contenido.exito === false) {
        throw new Error(contenido.mensaje || 'No se pudo completar la operación.');
    }

    return contenido;
}
