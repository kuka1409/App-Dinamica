<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Eliminar subtarea';
$rutaBase = '../';
$paginaActual = 'eliminar_subtarea';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page">
        <div class="section-heading">
            <p class="eyebrow">Subtareas</p>
            <h1>Eliminar subtarea</h1>
            <p>Selecciona el pendiente que deseas eliminar.</p>
        </div>

        <form class="stack-form form-card form-card--centrado" data-formulario="eliminar-subtarea">
            <div class="field">
                <label for="id_subtarea">Subtarea</label>
                <select id="id_subtarea" name="id_subtarea" data-select="subtareas" required>
                    <option value="">Cargando subtareas...</option>
                </select>
            </div>

            <button class="button button--danger" type="submit">Eliminar subtarea</button>
            <div class="form-message" data-mensaje="eliminar-subtarea" aria-live="polite"></div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
