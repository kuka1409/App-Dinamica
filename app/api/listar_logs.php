<?php
declare(strict_types=1);

require_once __DIR__ . '/comun.php';

requerir_metodo('GET');
$usuario = requerir_auditor_api();

try {
    $consulta = $conexion->query(
        'SELECT
            l.id,
            l.id_usuario,
            l.usuario_nombre,
            l.usuario_correo,
            l.usuario_rol,
            l.tipo_movimiento,
            l.ubicacion,
            l.tabla_afectada,
            l.id_registro,
            l.detalle,
            l.ip,
            l.sistema_operativo,
            l.navegador,
            l.user_agent,
            DATE_FORMAT(l.fecha_evento, "%d/%m/%Y") AS fecha_evento,
            DATE_FORMAT(l.fecha_evento, "%H:%i:%s") AS hora_evento,
            DATE_FORMAT(l.fecha_evento, "%d/%m/%Y, %H:%i:%s") AS fecha_hora
         FROM logs_sistema l
         ORDER BY l.fecha_evento DESC, l.id DESC
         LIMIT 150'
    );

    $logs = $consulta->fetchAll();

    registrar_log($conexion, (int) $usuario['id'], 'lectura', 'Consultar registro', 'logs_sistema', null, 'Auditor consultó los registros del sistema');

    respuesta_exitosa(['logs' => $logs]);
} catch (Throwable $excepcion) {
    respuesta_error('No se pudieron cargar los registros de auditoría.', 500);
}
