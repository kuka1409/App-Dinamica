<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Crear tarea';
$rutaBase = '../';
$paginaActual = 'crear_tarea';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page">
        <div class="section-heading">
            <p class="eyebrow">Tareas</p>
            <h1>Crear tarea</h1>
            <p>Registra una nueva tarea principal en tu cuenta.</p>
        </div>

        <form class="stack-form form-card form-card--centrado" data-formulario="crear-tarea">
            <div class="field">
                <label for="titulo">Nombre tarea</label>
                <input id="titulo" name="titulo" type="text" maxlength="120" placeholder="Ejemplo: Entrega del laboratorio" required>
            </div>

            <div class="field">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="5" maxlength="2000" placeholder="Objetivo o notas."></textarea>
            </div>

            <button class="button button--primary" type="submit">Crear tarea</button>
            <div class="form-message" data-mensaje="crear-tarea" aria-live="polite"></div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
