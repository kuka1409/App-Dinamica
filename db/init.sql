CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(180) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'auditor') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subtareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tarea INT NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    descripcion TEXT,
    completada BOOLEAN NOT NULL DEFAULT FALSE,
    posicion INT NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_tarea) REFERENCES tareas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    usuario_nombre VARCHAR(120) NULL,
    usuario_correo VARCHAR(180) NULL,
    usuario_rol VARCHAR(30) NULL,
    tipo_movimiento VARCHAR(80) NOT NULL,
    tabla_afectada VARCHAR(80) NULL,
    id_registro VARCHAR(80) NULL,
    detalle TEXT NULL,
    ip VARCHAR(64) NULL,
    sistema_operativo VARCHAR(120) NULL,
    navegador VARCHAR(120) NULL,
    user_agent TEXT NULL,
    fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (nombre, correo, contrasena_hash, rol)
VALUES (
    'Auditor del sistema',
    'auditor@taskmanager.local',
    '$2y$12$L1WK3x3YneLIoF6TarvJx.aVHSrh8kxcok46DXI9k4s6LpsyqZVMK',
    'auditor'
)
ON DUPLICATE KEY UPDATE rol = 'auditor';