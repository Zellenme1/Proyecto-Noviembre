-- El script comprueba que la columna no exista antes de ejecutar esta migracion.
USE trueques_db;
SET @columna_existe = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'curriculum'
);
SET @migracion = IF(
    @columna_existe = 0,
    'ALTER TABLE usuarios ADD COLUMN curriculum VARCHAR(255) NULL',
    'SELECT 1'
);
PREPARE migracion_curriculum FROM @migracion;
EXECUTE migracion_curriculum;
DEALLOCATE PREPARE migracion_curriculum;
-- Actualiza la tabla de usuarios para guardar la ruta del currículum.
