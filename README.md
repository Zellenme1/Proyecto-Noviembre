# Banco de Horas y Servicios

**Ego pro te, tu pro me**

Aplicación web de trueque de servicios profesionales mediante puntos. Cada profesional
puede ofrecer servicios, solicitar trabajos a otros miembros y completar trueques sin
usar dinero.

Proyecto integrador de Desarrollo de Aplicaciones Web (DAW).

## Funcionalidades

### Visitantes

- Consultar la portada y buscar ofertas.
- Filtrar profesionales por categoría y texto.
- Ordenar o consultar ofertas según las reseñas disponibles.
- Ver el perfil público de un profesional y sus valoraciones.
- Crear una cuenta profesional; se reciben 50 puntos de bienvenida.
- Elegir, opcionalmente, los servicios que se ofrecerán al registrarse.

### Profesionales

- Iniciar y cerrar sesión.
- Consultar el saldo actual y los puntos disponibles después de reservar solicitudes activas.
- Solicitar servicios a otros profesionales.
- Elegir una duración entre 30 minutos y 24 horas, en intervalos de media hora.
- Proponer una fecha y hora futuras.
- Ver las solicitudes enviadas y recibidas.
- Aceptar o rechazar solicitudes recibidas.
- Cancelar solicitudes propias enviadas cuando están pendientes o aceptadas.
- Indicar un motivo obligatorio al cancelar, con un máximo de 1000 caracteres.
- Marcar como hecho un servicio aceptado o en curso.
- Publicar una reseña de 1 a 5 estrellas y un comentario opcional al completar un servicio.
- Editar una reseña publicada.
- Subir una foto de perfil (JPG, PNG o WEBP, máximo 2 MB).
- Subir un currículum (PDF, DOC o DOCX, máximo 5 MB).
- Consultar el perfil propio y las reseñas recibidas.
- Eliminar la cuenta mediante contraseña; la operación realiza un borrado lógico y conserva el historial.

### Administradores

- Gestionar el catálogo global de servicios.
- Crear, editar y eliminar servicios.
- Asignar categoría, descripción y puntos de referencia por hora.
- Bloquear la eliminación de un servicio que tenga profesionales asociados.

## Tecnologías

- PHP 8.2 y programación orientada a objetos.
- Arquitectura MVC con patrón DAO.
- PDO y MySQL 8.
- Twig 3 para las plantillas HTML.
- JavaScript ES Modules y `fetch` para las llamadas a la API.
- CSS nativo y diseño responsive.
- Apache 2 con front controller.
- Docker y Docker Compose.
- Composer 2 para la carga automática de clases y Twig.

## Puesta en marcha

### Requisitos

- Docker Desktop con Docker Compose.

No es necesario instalar PHP, Composer, Apache ni MySQL en el equipo.

### Arranque

Desde la raíz del proyecto:

```powershell
docker compose up --build
```

Después abre:

- Aplicación: http://localhost:8080
- phpMyAdmin: http://localhost:8081

La base de datos MySQL se publica también en el puerto `3306`.

| Acción | Comando |
|---|---|
| Iniciar | `docker compose up` |
| Iniciar reconstruyendo imágenes | `docker compose up --build` |
| Detener conservando datos | `docker compose down` |
| Reiniciar desde cero | `docker compose down -v` y `docker compose up --build` |
| Ver errores de PHP/Apache | `docker compose logs web` |
| Ver el estado de los contenedores | `docker compose ps` |

> `docker compose down -v` elimina el volumen de MySQL y todos los datos guardados.
> Los scripts de `db/` solo se ejecutan al crear la base por primera vez.

### Usuarios de demostración

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | `admin@trueques.local` | `Admin1234!` |
| Profesional | `laura@trueques.local` | `Trueque1234!` |
| Profesional | `carlos@trueques.local` | `Trueque1234!` |
| Profesional | `marta@trueques.local` | `Trueque1234!` |

Estas cuentas son datos de prueba. Deben cambiarse o eliminarse antes de publicar la
aplicación en un entorno real.

## Arquitectura

```text
docker-compose.yml       Servicios web, MySQL y phpMyAdmin
Dockerfile               Imagen PHP 8.2 + Apache
apache.conf              DocumentRoot público y rutas del front controller
composer.json            Dependencias y autoload PSR-4

public/
  index.php              Punto de entrada de la aplicación
  css/styles.css         Estilos
  js/                    Módulos JavaScript por pantalla
  uploads/avatars/       Fotos de perfil
  uploads/curriculum/    Currículums subidos

src/
  Core/
    Auth.php             Sesión, usuario actual, roles y CSRF
    Csrf.php             Utilidades CSRF relacionadas con peticiones
    Router.php           Resolución de rutas y métodos HTTP
    Session.php          Ayudas de sesión
  Controllers/
    PaginaController.php Páginas Twig
    *ApiController.php   Endpoints JSON
  DAO/                   Acceso y operaciones con MySQL
  Models/                Usuario, Servicio y Categoría

templates/               Plantillas Twig
db/
  01-schema.sql          Creación de tablas
  02-seeds.sql           Datos y usuarios de demostración
  03-curriculum.sql      Migración idempotente del currículum
  04-motivo-cancelacion.sql Migración del motivo de cancelación
```

La separación principal es:

- El controlador decide qué operación se realiza.
- El DAO ejecuta las consultas preparadas y las transacciones.
- Los modelos representan los datos principales.
- Twig genera las vistas.
- JavaScript carga y modifica datos mediante la API.

## Flujo de una solicitud

1. El profesional busca una oferta en `/buscar`.
2. Introduce horas y fecha propuesta.
3. JavaScript valida la duración mínima/máxima y envía `POST /api/solicitudes`.
4. El servidor vuelve a validar los datos, calcula el coste y reserva puntos mediante una transacción.
5. El profesional receptor puede aceptar o rechazar la solicitud.
6. El prestador puede marcar el trabajo como hecho.
7. La finalización transferirá los puntos del cliente al prestador y registrará la fecha de completado.
8. El cliente podrá valorar el servicio completado y editar su reseña.

Los puntos se calculan con la tarifa por hora de la oferta y se redondean al alza. Las
solicitudes en estado `SOLICITADO`, `ACEPTADO` o `EN_EJECUCION` reservan puntos del
cliente para evitar comprometer más saldo del disponible.

### Estados de una solicitud

| Estado | Significado |
|---|---|
| `SOLICITADO` | Enviada y pendiente de respuesta |
| `ACEPTADO` | El prestador la aceptó |
| `RECHAZADO` | El prestador la rechazó |
| `EN_EJECUCION` | Estado utilizado por el modelo de datos para trabajos iniciados |
| `COMPLETADO` | Finalizada y con puntos transferidos |
| `CANCELADO` | Cancelada por el cliente con motivo |

Las acciones están protegidas por rol y por la participación del usuario en la solicitud.
Las operaciones de cambio de estado comprueban el estado anterior para evitar conflictos
si dos personas actúan al mismo tiempo.

## Rutas principales

### Páginas

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/` | Público |
| `GET` | `/login` | Solo visitante |
| `GET` | `/registro` | Solo visitante |
| `GET` | `/buscar` | Público |
| `GET` | `/profesionales/{id}` | Público |
| `GET` | `/perfil` | Usuario autenticado |
| `GET` | `/solicitudes` | Profesional |
| `GET` | `/servicios-spa` | Administrador |

### API

| Método | Ruta | Función |
|---|---|---|
| `POST` | `/api/login` | Iniciar sesión |
| `POST` | `/api/logout` | Cerrar sesión |
| `POST` | `/api/registro` | Registrar profesional |
| `POST` | `/api/foto` | Subir foto de perfil |
| `POST` | `/api/curriculum` | Subir currículum |
| `DELETE` | `/api/perfil` | Desactivar la cuenta |
| `GET` | `/api/categorias` | Listar categorías |
| `GET` | `/api/ofertas?id_categoria=&q=` | Buscar ofertas públicas |
| `GET` | `/api/profesionales/{id}/resenas` | Resumen y reseñas públicas |
| `GET` | `/api/servicios` | Listar catálogo |
| `GET` | `/api/servicios/{id}` | Consultar servicio |
| `POST` | `/api/servicios` | Crear servicio; solo administrador |
| `PUT` | `/api/servicios/{id}` | Editar servicio; solo administrador |
| `DELETE` | `/api/servicios/{id}` | Eliminar servicio; solo administrador |
| `GET` | `/api/solicitudes` | Solicitudes enviadas y recibidas |
| `GET` | `/api/puntos-disponibles` | Saldo disponible tras reservas |
| `POST` | `/api/solicitudes` | Crear solicitud |
| `PUT` | `/api/solicitudes/{id}` | Aceptar, rechazar, cancelar o completar |
| `POST` | `/api/solicitudes/{id}/valoracion` | Crear reseña |
| `PUT` | `/api/solicitudes/{id}/valoracion` | Editar reseña |

Todas las rutas que modifican datos requieren el token CSRF de la sesión.

## Base de datos

El esquema está compuesto por seis tablas principales:

- `usuarios`
- `categorias`
- `servicios`
- `ofertas_profesionales`
- `solicitudes_trueque`
- `valoraciones`

Datos añadidos o ampliados respecto a un esquema básico:

- `usuarios.activo`: borrado lógico de cuentas.
- `usuarios.foto`: ruta pública de la foto.
- `usuarios.curriculum`: ruta pública del currículum.
- `solicitudes_trueque.fecha_completado`: fecha real de finalización.
- `solicitudes_trueque.motivo_cancelacion`: motivo escrito por el cliente.

`Database.php` comprueba y añade automáticamente las columnas `curriculum` y
`motivo_cancelacion` si faltan en una base ya creada.

## Seguridad

- Contraseñas cifradas con `password_hash` y verificadas con `password_verify`.
- Respuesta uniforme ante email inexistente, contraseña incorrecta o cuenta desactivada.
- Sesiones PHP con cookie `HttpOnly`, `SameSite=Lax` y `Strict mode`.
- Regeneración del identificador de sesión al iniciar sesión.
- Token CSRF en todas las peticiones `POST`, `PUT` y `DELETE`.
- Comprobación de roles en el servidor.
- Usuario de sesión y datos sensibles obtenidos o calculados en servidor.
- Consultas SQL preparadas mediante PDO.
- Transacciones para reservar puntos y transferirlos al completar.
- Validación de fotos por tipo MIME y dimensiones reales.
- Validación de currículums por extensión y contenido MIME real.
- Nombres aleatorios para los archivos subidos.
- DocumentRoot limitado a `public/`.
- Las cuentas desactivadas dejan de aparecer en búsquedas y pierden acceso a la sesión.
- Eliminación de archivos de foto o currículum sustituidos para evitar huérfanos.

## Configuración

El contenedor web recibe la conexión mediante estas variables:

| Variable | Valor Compose |
|---|---|
| `DB_HOST` | `db` |
| `DB_NAME` | `trueques_db` |
| `DB_USER` | `usuario_trueque` |
| `DB_PASS` | `password_secret` |

Los parámetros por defecto coinciden con el entorno Docker definido en
`docker-compose.yml`. Para cambiar las credenciales hay que modificar el entorno de
ambos contenedores y reconstruir o recrear los servicios.

## Desarrollo

Los cambios en `src/`, `public/`, `templates/` y `db/` se sirven desde volúmenes en
desarrollo y no requieren reconstruir la imagen web en la mayoría de los casos.

Reinicia la imagen solo después de cambios en:

- `Dockerfile`
- `apache.conf`
- `composer.json`
- `docker-compose.yml`

Comandos útiles:

```powershell
docker compose up --build
docker compose logs -f web
docker compose exec web php -v
docker compose exec db mysql -u usuario_trueque -ppassword_secret trueques_db
```

No hay una suite automatizada de pruebas incluida actualmente.

## Uso de herramientas de inteligencia artificial

Durante el desarrollo se ha utilizado **Copilot SDK en VS Code** como herramienta de
asistencia para:

- redactar y actualizar documentación;
- revisar código y detectar posibles errores;
- generar ideas y ejemplos técnicos;
- ayudar en la resolución de problemas de desarrollo.

Las decisiones finales, las modificaciones y las pruebas han sido realizadas y
revisadas por el equipo participante. La IA no sustituye la comprensión del código ni
las comprobaciones manuales sobre Docker, PHP, JavaScript y MySQL.
