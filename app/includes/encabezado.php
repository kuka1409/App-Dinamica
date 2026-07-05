<?php
/** @var string $tituloPagina */
/** @var string $paginaActual */
/** @var string $rutaBase */
require_once __DIR__ . '/funciones.php';
$usuarioSesion = usuario_autenticado();
$tituloPagina = $tituloPagina ?? 'Gestor personal de Tareas';
$paginaActual = $paginaActual ?? '';
$rutaBase = $rutaBase ?? '';
?>
<!DOCTYPE html>
<html lang="es" data-base-url="<?= escapar($rutaBase) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($tituloPagina) ?></title>
    <link rel="stylesheet" href="<?= escapar($rutaBase) ?>assets/css/styles.css">
    <script defer src="<?= escapar($rutaBase) ?>assets/js/estado.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/utilidades.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/mensajes.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/api.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/acciones_formulario.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/sesion.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/datos.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/selectores.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/tareas_formularios.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/subtareas_formularios.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/tareas_listado.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/subtareas_listado.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/auditoria.js"></script>
    <script defer src="<?= escapar($rutaBase) ?>assets/js/app.js"></script>
</head>
<body>
    <div class="app-shell app-shell--sin-sidebar">
        <div class="workspace workspace--full">
            <header class="topbar topbar--principal">
                <a class="sidebar__brand sidebar__brand--horizontal" href="<?= escapar($rutaBase) ?>index.php" aria-label="Ir al inicio">
                    <img class="brand-logo" src="<?= escapar($rutaBase) ?>assets/img/taskmanager_logo.webp" alt="TaskManager">
                    <span class="brand-copy">
                        <strong>Gestor personal de Tareas</strong>
                        <span>Organiza tus pendientes</span>
                    </span>
                </a>

                <?php if ($usuarioSesion !== null): ?>
                    <nav class="nav" aria-label="Navegación principal">
                        <a class="nav__link<?= clase_menu_activa($paginaActual, 'inicio') ?>" href="<?= escapar($rutaBase) ?>index.php">
                            <span class="nav__top-icon nav__top-icon--inicio" aria-hidden="true"></span>
                            <span>Inicio</span>
                        </a>

                        <div class="nav__dropdown">
                            <button class="nav__link nav__button" type="button" aria-haspopup="true" aria-expanded="false">
                                <span class="nav__top-icon nav__top-icon--gestion" aria-hidden="true"></span>
                                <span>Gestión</span>
                            </button>
                            <div class="nav__menu" role="menu">
                                <span class="nav__menu-title">Tareas</span>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/tareas_crear.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--crear" aria-hidden="true"></span>
                                    <span>Crear tarea</span>
                                </a>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/tareas_listar.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--ver" aria-hidden="true"></span>
                                    <span>Ver tareas</span>
                                </a>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/tareas_actualizar.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--editar" aria-hidden="true"></span>
                                    <span>Actualizar tarea</span>
                                </a>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/tareas_eliminar.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--eliminar" aria-hidden="true"></span>
                                    <span>Eliminar tarea</span>
                                </a>

                                <span class="nav__menu-title">Subtareas</span>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/subtareas_crear.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--crear" aria-hidden="true"></span>
                                    <span>Crear subtarea</span>
                                </a>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/subtareas_listar.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--ver" aria-hidden="true"></span>
                                    <span>Ver subtareas</span>
                                </a>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/subtareas_actualizar.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--editar" aria-hidden="true"></span>
                                    <span>Actualizar subtarea</span>
                                </a>
                                <a class="nav__menu-link" href="<?= escapar($rutaBase) ?>paginas/subtareas_eliminar.php" role="menuitem">
                                    <span class="nav__menu-icon nav__menu-icon--eliminar" aria-hidden="true"></span>
                                    <span>Eliminar subtarea</span>
                                </a>
                            </div>
                        </div>

                        <?php if (usuario_es_auditor()): ?>
                            <a class="nav__link<?= clase_menu_activa($paginaActual, 'auditoria') ?>" href="<?= escapar($rutaBase) ?>paginas/auditoria.php">
                                <span class="nav__top-icon nav__top-icon--auditoria" aria-hidden="true"></span>
                                <span>Auditoría</span>
                            </a>
                        <?php endif; ?>
                    </nav>

                    <div class="topbar__usuario">
                        <span><?= escapar((string) $usuarioSesion['nombre']) ?></span>
                        <small><?= escapar((string) $usuarioSesion['rol']) ?></small>
                        <button class="button button--ghost button--small" type="button" data-accion="cerrar-sesion">
                            <span class="button__icon button__icon--cerrar-sesion" aria-hidden="true"></span>
                            <span>Salir</span>
                        </button>
                    </div>
                <?php endif; ?>
            </header>
