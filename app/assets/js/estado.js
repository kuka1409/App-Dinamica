// Estado compartido del frontend.
const rutaBase = document.documentElement.dataset.baseUrl || '';

const estado = {
    tareas: [],
    subtareas: [],
    idsTareasExpandidas: new Set(),
};
