-- Anade el motivo de cancelacion a solicitudes existentes.
USE trueques_db;
SET @columna_existe = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'solicitudes_trueque'
      AND COLUMN_NAME = 'motivo_cancelacion'
);
SET @migracion = IF(
    @columna_existe = 0,
    'ALTER TABLE solicitudes_trueque ADD COLUMN motivo_cancelacion TEXT NULL',
    'SELECT 1'
);
PREPARE migracion_cancelacion FROM @migracion;
EXECUTE migracion_cancelacion;
DEALLOCATE PREPARE migracion_cancelacion;
