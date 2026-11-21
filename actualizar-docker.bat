@echo off
setlocal
cd /d "%~dp0"

echo === Actualizando Docker ===
docker compose down
if errorlevel 1 (
    echo.
    echo Error: no se pudo detener Docker o Docker Desktop no esta en marcha.
    exit /b 1
)

docker compose up --build -d
if errorlevel 1 (
    echo.
    echo Error: no se pudo construir o iniciar los contenedores.
    exit /b 1
)

echo.
echo Esperando a MySQL...
set "intentos=0"
:esperar_mysql
docker compose exec -T db mysqladmin ping -h localhost -uroot -proot_password --silent >nul 2>&1
if not errorlevel 1 goto mysql_listo
set /a intentos+=1
if %intentos% GEQ 60 (
    echo.
    echo Error: MySQL no respondio a tiempo.
    exit /b 1
)
timeout /t 2 /nobreak >nul
goto esperar_mysql

:mysql_listo
echo Comprobando la migracion del curriculum...
set "columna_curriculum="
for /f %%C in ('docker compose exec -T db mysql -uroot -proot_password trueques_db -Nse "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 0x7573756172696f73 AND COLUMN_NAME = 0x637572726963756c756d"') do set "columna_curriculum=%%C"
if not defined columna_curriculum (
    echo.
    echo Error: no se pudo comprobar el esquema de la base de datos.
    exit /b 1
)
if "%columna_curriculum%"=="0" goto aplicar_migracion
goto migracion_lista

:aplicar_migracion
echo Aplicando la migracion del curriculum...
docker compose exec -T db mysql -uroot -proot_password trueques_db < db\03-curriculum.sql
if errorlevel 1 (
    echo.
    echo Error: no se pudo aplicar db\03-curriculum.sql.
    exit /b 1
)

:migracion_lista
echo Esquema de usuarios actualizado.

echo Comprobando el motivo de cancelacion...
set "columna_motivo="
for /f %%C in ('docker compose exec -T db mysql -uroot -proot_password trueques_db -Nse "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 0x736f6c69636974756465735f74727565717565 AND COLUMN_NAME = 0x6d6f7469766f5f63616e63656c6163696f6e"') do set "columna_motivo=%%C"
if not defined columna_motivo (
    echo.
    echo Error: no se pudo comprobar el esquema de solicitudes.
    exit /b 1
)
if "%columna_motivo%"=="0" goto aplicar_migracion_cancelacion
goto migraciones_completas

:aplicar_migracion_cancelacion
echo Aplicando la migracion del motivo de cancelacion...
docker compose exec -T db mysql -uroot -proot_password trueques_db < db\04-motivo-cancelacion.sql
if errorlevel 1 (
    echo.
    echo Error: no se pudo aplicar db\04-motivo-cancelacion.sql.
    exit /b 1
)

:migraciones_completas
echo Esquema de la base de datos actualizado.

echo.
echo Docker actualizado correctamente.
echo Abre http://localhost:8080
exit /b 0
