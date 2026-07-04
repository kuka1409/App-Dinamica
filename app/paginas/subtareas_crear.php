<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Crear subtarea';
$rutaBase = '../';
$paginaActual = 'crear_subtarea';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page">
        <div class="section-heading">
            <p class="eyebrow">Subtareas</p>
            <h1>Crear subtarea</h1>
            <p>Agrega un pendiente dentro de una tarea principal.</p>
        </div>

        <form class="stack-form form-card form-card--centrado" data-formulario="crear-subtarea">
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

            <button class="button button--primary" type="submit">Crear subtarea</button>
            <div class="form-message" data-mensaje="crear-subtarea" aria-live="polite"></div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
