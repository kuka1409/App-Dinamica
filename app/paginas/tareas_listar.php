<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_usuario_pagina();
$tituloPagina = 'Ver tareas';
$rutaBase = '../';
$paginaActual = 'listar_tareas';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page crud-page--wide">
        <div class="section-heading">
            <p class="eyebrow">Tareas</p>
            <h1>Ver tareas</h1>
            <p>Revisa las tareas registradas en tu cuenta.</p>
        </div>

        <div class="page-message" data-mensaje="listar-tareas" aria-live="polite"></div>
        <div class="group-list" data-lista="tareas"></div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
