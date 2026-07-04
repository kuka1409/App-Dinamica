<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('GET');

respuesta_exitosa(['usuario' => usuario_autenticado()]);
