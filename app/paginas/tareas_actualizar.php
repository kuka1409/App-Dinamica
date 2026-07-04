<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Actualizar tarea';
$rutaBase = '../';
$paginaActual = 'actualizar_tarea';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page">
        <div class="section-heading">
            <p class="eyebrow">Tareas</p>
            <h1>Actualizar tarea</h1>
            <p>Selecciona una tarea y modifica su información.</p>
        </div>

        <form class="stack-form form-card form-card--centrado" data-formulario="actualizar-tarea">
            <div class="field">
                <label for="id_tarea">Tarea</label>
                <select id="id_tarea" name="id_tarea" data-select="tareas" required>
                    <option value="">Cargando tareas...</option>
                </select>
            </div>

            <div class="field">
                <label for="titulo">Nombre tarea</label>
                <input id="titulo" name="titulo" type="text" maxlength="120" required>
            </div>

            <div class="field">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="5" maxlength="2000"></textarea>
            </div>

            <button class="button button--primary" type="submit">Guardar cambios</button>
            <div class="form-message" data-mensaje="actualizar-tarea" aria-live="polite"></div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
