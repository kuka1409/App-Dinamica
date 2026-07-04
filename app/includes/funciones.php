<?php
declare(strict_types=1);

function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function usuario_autenticado(): ?array
{
    iniciar_sesion_segura();

    if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario'])) {
        return null;
    }

    return $_SESSION['usuario'];
}

function usuario_esta_autenticado(): bool
{
    return usuario_autenticado() !== null;
}

function usuario_es_auditor(): bool
{
    $usuario = usuario_autenticado();
    return $usuario !== null && ($usuario['rol'] ?? '') === 'auditor';
}

function requerir_usuario_pagina(): array
{
    $usuario = usuario_autenticado();

    if ($usuario === null) {
        header('Location: /index.php');
        exit;
    }

    return $usuario;
}

function requerir_auditor_pagina(): array
{
    $usuario = requerir_usuario_pagina();

    if (($usuario['rol'] ?? '') !== 'auditor') {
        http_response_code(403);
        exit('Acceso permitido solo para auditores.');
    }

    return $usuario;
}

function clase_menu_activa(string $paginaActual, string $paginaEsperada): string
{
    return $paginaActual === $paginaEsperada ? ' nav__link--active' : '';
}
