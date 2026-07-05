-- Parche para bases de datos ya creadas con la version anterior.
-- Cambia fecha_evento a DATETIME para que el log conserve exactamente
-- la hora calculada por PHP en America/Santiago y no la convierta a UTC.

ALTER TABLE logs_sistema
    MODIFY fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
