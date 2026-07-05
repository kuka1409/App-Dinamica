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

        <form class="audit-filters audit-filters--compact" data-filtros-auditoria autocomplete="off">
            <div class="audit-filters__grid audit-filters__grid--single-line">
                <label class="field field--compact">
                    <span>Desde</span>
                    <input type="date" name="fecha_desde">
                </label>

                <label class="field field--compact">
                    <span>Hasta</span>
                    <input type="date" name="fecha_hasta">
                </label>

                <label class="field field--compact">
                    <span>Usuario</span>
                    <input type="search" name="usuario" maxlength="120" placeholder="Nombre o correo">
                </label>

                <label class="field field--compact">
                    <span>Movimiento</span>
                    <select name="tipo_movimiento">
                        <option value="">Todos</option>
                        <option value="creacion">Creación</option>
                        <option value="actualizacion">Actualización</option>
                        <option value="eliminacion">Eliminación</option>
                        <option value="lectura">Lectura</option>
                        <option value="inicio_sesion">Inicio sesión</option>
                        <option value="cierre_sesion">Cierre sesión</option>
                    </select>
                </label>

                <label class="field field--compact">
                    <span>Módulo</span>
                    <select name="modulo">
                        <option value="">Todos</option>
                        <option value="Usuarios">Usuarios</option>
                        <option value="Sesión">Sesión</option>
                        <option value="Tareas">Tareas</option>
                        <option value="Subtareas">Subtareas</option>
                        <option value="Auditoría">Auditoría</option>
                    </select>
                </label>

                <label class="field field--compact">
                    <span>Tabla</span>
                    <select name="tabla_afectada">
                        <option value="">Todas</option>
                        <option value="usuarios">usuarios</option>
                        <option value="tareas">tareas</option>
                        <option value="subtareas">subtareas</option>
                        <option value="logs_sistema">logs_sistema</option>
                    </select>
                </label>
            </div>

            <div class="audit-filters__status-row">
                <div class="audit-filters__actions">
                    <button class="button button--primary button--filter" type="submit">Aplicar filtros</button>
                    <button class="button button--ghost button--filter" type="button" data-accion="limpiar-filtros-auditoria">Limpiar filtros</button>
                </div>
                <div class="audit-filters__applied" data-filtros-auditoria-aplicados>Filtros aplicados: ninguno.</div>
            </div>
        </form>

        <div class="page-message" data-mensaje="auditoria" aria-live="polite"></div>
        <div class="group-list" data-lista="logs"></div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/pie.php'; ?>
