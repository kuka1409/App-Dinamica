<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Eliminar tarea';
$rutaBase = '../';
$paginaActual = 'eliminar_tarea';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page">
        <div class="section-heading">
            <p class="eyebrow">Tareas</p>
            <h1>Eliminar tarea</h1>
            <p>Selecciona la tarea que deseas eliminar. Esta acción también eliminará sus subtareas.</p>
        </div>

        <form class="stack-form form-card form-card--centrado" data-formulario="eliminar-tarea">
            <div class="field">
                <label for="id_tarea">Tarea</label>
                <select id="id_tarea" name="id_tarea" data-select="tareas" required>
                    <option value="">Cargando tareas...</option>
                </select>
            </div>

            <button class="button button--danger" type="submit">Eliminar tarea</button>
            <div class="form-message" data-mensaje="eliminar-tarea" aria-live="polite"></div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
