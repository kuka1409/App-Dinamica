<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requerir_auditor_pagina();
$tituloPagina = 'Auditoría';
$rutaBase = '../';
$paginaActual = 'auditoria';
require_once __DIR__ . '/../includes/encabezado.php';
?>
<main class="content content--centrado">
    <section class="crud-page crud-page--wide">
        <div class="section-heading">
            <p class="eyebrow">Auditoría</p>
            <h1>Registros del sistema</h1>
            <p>Esta sección solo puede ser revisada por cuentas con rol auditor.</p>
        </div>

        <div class="page-message" data-mensaje="auditoria" aria-live="polite"></div>
        <div class="group-list" data-lista="logs"></div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
