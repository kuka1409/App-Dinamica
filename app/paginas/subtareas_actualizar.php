<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Actualizar subtarea';
$rutaBase = '../';
$paginaActual = 'actualizar_subtarea';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page">
        <div class="section-heading">
            <p class="eyebrow">Subtareas</p>
            <h1>Actualizar subtarea</h1>
            <p>Selecciona una subtarea y modifica su información.</p>
        </div>

        <form class="stack-form form-card form-card--centrado" data-formulario="actualizar-subtarea">
            <div class="field">
                <label for="id_subtarea">Subtarea</label>
                <select id="id_subtarea" name="id_subtarea" data-select="subtareas" required>
                    <option value="">Cargando subtareas...</option>
                </select>
            </div>

            <div class="field">
                <label for="id_tarea">Tarea principal</label>
                <select id="id_tarea" name="id_tarea" data-select="tareas" required>
                    <option value="">Cargando tareas...</option>
                </select>
            </div>

            <div class="field">
                <label for="titulo">Nombre subtarea</label>
                <input id="titulo" name="titulo" type="text" maxlength="160" required>
            </div>

            <div class="field">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="5" maxlength="2000"></textarea>
            </div>

            <label class="checkbox-field">
                <input name="completada" type="checkbox" value="1">
                <span>Subtarea completada</span>
            </label>

            <button class="button button--primary" type="submit">Guardar cambios</button>
            <div class="form-message" data-mensaje="actualizar-subtarea" aria-live="polite"></div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
