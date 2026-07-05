/**
 * Archivo: mensajes.js
 *
 * Este archivo contiene utilidades para mostrar mensajes visuales en pantalla.
 * Se usa para respuestas de exito, errores de formularios y avisos de auditoria.
 *
 * Funciones principales:
 * - Buscar contenedores de mensajes.
 * - Mostrar mensajes con clase visual.
 * - Limpiar mensajes anteriores.
 */

/**
 * Proposito:
 * Busca el contenedor de mensajes asociado a un nombre logico.
 *
 * Uso:
 * Se usa antes de mostrar mensajes de exito o error en formularios y vistas.
 *
 * Argumentos:
 * - nombre {string}: Valor usado en el atributo data-mensaje.
 *
 * Retorna:
 * - {HTMLElement|null}: Contenedor encontrado o null si no existe.
 *
 * Flujo:
 * 1. Construye un selector usando data-mensaje.
 * 2. Busca el primer elemento que coincida en el documento.
 * 3. Retorna el elemento encontrado o null.
 */
function obtenerMensaje(nombre) {
    return document.querySelector(`[data-mensaje="${nombre}"]`);
}

/**
 * Proposito:
 * Muestra un mensaje visual en un contenedor especifico.
 *
 * Uso:
 * Se usa para informar exito, errores o avisos al usuario.
 *
 * Argumentos:
 * - elemento {HTMLElement|null}: Contenedor donde se mostrara el mensaje.
 * - texto {string}: Mensaje que se quiere mostrar.
 * - tipo {string}: Tipo visual del mensaje. Por defecto es success.
 *
 * Retorna:
 * - {void}: No retorna datos. Modifica texto y clases CSS del elemento.
 *
 * Flujo:
 * 1. Verifica que el contenedor exista.
 * 2. Asigna el texto del mensaje.
 * 3. Define las clases visuales segun el tipo recibido.
 */
function mostrarMensaje(elemento, texto, tipo = 'success') {
    if (!elemento) {
        return;
    }

    elemento.textContent = texto;
    elemento.classList.remove('is-success', 'is-error');
    elemento.classList.add(tipo === 'error' ? 'is-error' : 'is-success');
}

/**
 * Proposito:
 * Limpia el texto y las clases visuales de un contenedor de mensajes.
 *
 * Uso:
 * Se usa cuando una accion termina correctamente o antes de mostrar nuevos datos.
 *
 * Argumentos:
 * - elemento {HTMLElement|null}: Contenedor que se limpiara.
 *
 * Retorna:
 * - {void}: No retorna datos. Modifica el DOM si el elemento existe.
 *
 * Flujo:
 * 1. Verifica que el contenedor exista.
 * 2. Borra el texto visible.
 * 3. Restaura la clase base del mensaje.
 */
function limpiarMensaje(elemento) {
    if (!elemento) {
        return;
    }

    elemento.textContent = '';
    elemento.classList.remove('is-success', 'is-error');
}
