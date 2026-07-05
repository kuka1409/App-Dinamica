/**
 * Archivo: estado.js
 *
 * Este archivo define valores globales usados por varios modulos del frontend.
 * Mantiene la ruta base del proyecto y el estado compartido con tareas,
 * subtareas y tarjetas expandidas.
 *
 * Contiene:
 * - rutaBase: ruta base calculada desde el HTML.
 * - estado: objeto compartido por los archivos JS.
 */

/**
 * Proposito:
 * Guarda la ruta base del proyecto para construir URLs relativas desde JavaScript.
 *
 * Uso:
 * Se usa al crear enlaces y peticiones hacia paginas o endpoints de la aplicacion.
 */
const rutaBase = document.documentElement.dataset.baseUrl || '';

/**
 * Proposito:
 * Mantiene datos compartidos entre los modulos del frontend.
 *
 * Uso:
 * Se usa para guardar tareas, subtareas y tareas expandidas sin volver a consultar el DOM.
 */
const estado = {
    tareas: [],
    subtareas: [],
    idsTareasExpandidas: new Set(),
};
