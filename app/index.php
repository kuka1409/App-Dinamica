<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/funciones.php';
$tituloPagina = 'Gestor personal de Tareas';
$paginaActual = 'inicio';
$usuario = usuario_autenticado();
require_once __DIR__ . '/includes/encabezado.php';
?>

<main class="content content--centrado">
    <?php if ($usuario === null): ?>
        <section class="auth-layout" aria-label="Acceso de usuarios">
            <div class="content__hero-copy auth-copy">
                <p class="eyebrow">Bienvenido</p>
                <h1>Gestor personal de Tareas</h1>
                <p>Inicia sesión o crea una cuenta para organizar tus pendientes de forma privada.</p>
            </div>

            <div class="auth-panels">
                <section class="sidebar__panel auth-panel">
                    <div class="section-heading section-heading--tight">
                        <h2>Iniciar sesión</h2>
                        <p>Accede con tu correo y contraseña.</p>
                    </div>

                    <form class="stack-form" data-formulario="login">
                        <div class="field">
                            <label for="login-correo">Correo</label>
                            <input id="login-correo" name="correo" type="email" maxlength="180" required>
                        </div>

                        <div class="field">
                            <label for="login-contrasena">Contraseña</label>
                            <input id="login-contrasena" name="contrasena" type="password" minlength="6" required>
                        </div>

                        <button class="button button--primary" type="submit">Ingresar</button>
                        <div class="form-message" data-mensaje="login" aria-live="polite"></div>
                    </form>
                </section>

                <section class="sidebar__panel auth-panel">
                    <div class="section-heading section-heading--tight">
                        <h2>Crear cuenta</h2>
                        <p>Crea tu cuenta para comenzar a organizar tus tareas.</p>
                    </div>

                    <form class="stack-form" data-formulario="registro">
                        <div class="field">
                            <label for="registro-nombre">Nombre</label>
                            <input id="registro-nombre" name="nombre" type="text" maxlength="120" required>
                        </div>

                        <div class="field">
                            <label for="registro-correo">Correo</label>
                            <input id="registro-correo" name="correo" type="email" maxlength="180" required>
                        </div>

                        <div class="field">
                            <label for="registro-contrasena">Contraseña</label>
                            <input id="registro-contrasena" name="contrasena" type="password" minlength="6" required>
                        </div>


                        <button class="button button--secondary" type="submit">Registrarme</button>
                        <div class="form-message" data-mensaje="registro" aria-live="polite"></div>
                    </form>
                </section>
            </div>
        </section>
    <?php else: ?>
        <section class="content__hero content__hero--inicio">
            <div class="content__hero-copy">
                <p class="eyebrow">Panel principal</p>
                <h1>Bienvenido, <?= escapar((string) $usuario['nombre']) ?></h1>
                <p>Desde el menú superior puedes crear, revisar, actualizar y eliminar tus tareas y subtareas de forma ordenada.</p>
            </div>
        </section>

        <img class="imagen-inicio" src="assets/img/imagen-inicio.webp" alt="Imagen de bienvenida">
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/pie.php'; ?>
