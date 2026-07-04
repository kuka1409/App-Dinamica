-- Parche opcional para bases de datos ya creadas.
-- Úsalo si NO quieres borrar el volumen de MySQL.
-- Deja la columna del log preparada para recibir la hora local enviada por PHP.

ALTER TABLE logs_sistema
    MODIFY fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
