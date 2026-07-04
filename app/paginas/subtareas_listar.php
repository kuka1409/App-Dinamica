<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Ver subtareas';
$rutaBase = '../';
$paginaActual = 'listar_subtareas';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page crud-page--wide">
        <div class="section-heading">
            <p class="eyebrow">Subtareas</p>
            <h1>Ver subtareas</h1>
            <p>Revisa los pendientes asociados a tus tareas.</p>
        </div>

        <div class="page-message" data-mensaje="listar-subtareas" aria-live="polite"></div>
        <div class="group-list" data-lista="subtareas"></div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
