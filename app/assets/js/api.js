/**
 * Archivo: api.js
 *
 * Este archivo centraliza la comunicacion entre el frontend y los endpoints PHP.
 * Permite que otros modulos hagan peticiones HTTP sin repetir la logica de fetch.
 *
 * Funciones principales:
 * - Construir la URL final de cada endpoint.
 * - Enviar datos al backend en formato JSON.
 * - Leer respuestas JSON.
 * - Lanzar errores cuando la API responde con fallo.
 */

/**
 * Proposito:
 * Centraliza las peticiones HTTP que el frontend realiza hacia los endpoints PHP.
 *
 * Uso:
 * Se usa desde los demas archivos JS para listar, crear, actualizar o eliminar datos.
 *
 * Argumentos:
 * - url {string}: Ruta del endpoint PHP o URL absoluta.
 * - metodo {string}: Metodo HTTP usado por fetch. Por defecto es GET.
 * - datos {object|null}: Datos que se enviaran al backend. Por defecto es null.
 *
 * Retorna:
 * - {Promise<object>}: Respuesta JSON convertida en objeto JavaScript.
 *
 * Flujo:
 * 1. Construye la URL final usando rutaBase si la ruta es relativa.
 * 2. Prepara las opciones basicas para fetch.
 * 3. Si hay datos, los convierte a JSON y agrega Content-Type.
 * 4. Envia la peticion al backend.
 * 5. Intenta leer la respuesta como JSON.
 * 6. Si la respuesta falla, lanza un error con el mensaje recibido.
 * 7. Si todo esta correcto, retorna el contenido.
 */
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
