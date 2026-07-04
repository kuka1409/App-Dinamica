// Manejo de mensajes visuales de formularios y listados.

function obtenerMensaje(nombre) {
    return document.querySelector(`[data-mensaje="${nombre}"]`);
}

function mostrarMensaje(elemento, texto, tipo = 'success') {
    if (!elemento) {
        return;
    }

    elemento.textContent = texto;
    elemento.classList.remove('is-success', 'is-error');
    elemento.classList.add(tipo === 'error' ? 'is-error' : 'is-success');
}

function limpiarMensaje(elemento) {
    if (!elemento) {
        return;
    }

    elemento.textContent = '';
    elemento.classList.remove('is-success', 'is-error');
}
