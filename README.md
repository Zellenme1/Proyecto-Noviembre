# Banco de Horas y Servicios («Ego pro te, tu pro me»)

Plataforma web de trueque de servicios profesionales por puntos. Cada hora que un
profesional presta le da puntos, y con esos puntos pide servicios a cualquier otro
miembro de la comunidad. Proyecto integrador de DAW.

**Tecnologías:** PHP 8 orientado a objetos (MVC + DAO), Twig, MySQL, Apache en Docker,
Composer y JavaScript vanilla con `fetch`. CSS nativo, sin frameworks.

---

## 1. Puesta en marcha

Solo hace falta tener **Docker Desktop** abierto.

```
docker compose up --build
```

Cuando en el log salga `ready for connections` (de MySQL), abre **http://localhost:8080**.

| Para...                                        | Comando                                      |
|------------------------------------------------|----------------------------------------------|
| Parar (conserva los datos)                     | `Ctrl + C` y después `docker compose down`   |
| Volver a arrancar                              | `docker compose up`                          |
| **Reiniciar la base de datos desde cero**      | `docker compose down -v` y `docker compose up --build` |
| Ver los errores de PHP                         | `docker compose logs web`                    |

> Los scripts de `db/` solo se ejecutan cuando la base se crea. Si cambias
> `db/01-schema.sql` o `db/02-seeds.sql`, recuerda que no se aplican a una base ya
> creada. Para conservar sus datos, aplica los cambios de esquema desde phpMyAdmin;
> `docker compose down -v` borra la base y debe reservarse para un reinicio deliberado.
> La hora se registra en las nuevas finalizaciones; las solicitudes completadas
> antes de añadir la columna no tienen una hora real que se pueda recuperar.

### Usuarios de prueba

| Rol           | Email                   | Contraseña     |
|---------------|-------------------------|----------------|
| Administrador | `admin@trueques.local`  | `Admin1234!`   |
| Profesional   | `laura@trueques.local`  | `Trueque1234!` |
| Profesional   | `carlos@trueques.local` | `Trueque1234!` |
| Profesional   | `marta@trueques.local`  | `Trueque1234!` |

Son cuentas de desarrollo: hay que cambiarlas o quitarlas antes de publicar nada.

### Si algo falla

| Síntoma                                           | Causa y solución |
|---------------------------------------------------|------------------|
| `port is already allocated` (3306 u 8080)         | Otro programa usa el puerto (por ejemplo el MySQL de XAMPP). Páralo o cambia el puerto de la izquierda en `docker-compose.yml`. |
| «Email o contraseña incorrectos» con los de prueba | La base tiene datos antiguos: `docker compose down -v` y arrancar otra vez. |
| La web da error al arrancar                       | MySQL aún está iniciando: espera unos segundos y recarga. |
| Cambios en `src/`, `public/` o `templates/` no se ven | Se ven al recargar (`Ctrl + F5`). Solo `Dockerfile`, `apache.conf` o `composer.json` piden `docker compose up --build`. |

---

## 2. Qué puede hacer cada usuario

- **Visitante:** ver la portada, **buscar servicios** (filtrar por categoría o texto, ordenados por
  valoración), **registrarse** (50 puntos de bienvenida, servicios que ofrece y foto opcionales) e iniciar sesión.
- **Profesional:** todo lo anterior y **solicitar servicios** a otros profesionales, y en
  **Mis solicitudes** ver las que ha hecho y las que recibe, con aceptar, rechazar y cancelar.
  Los puntos de las solicitudes sin completar se reservan para impedir que se comprometa
  más saldo del disponible. Tras completar un servicio, quien lo solicitó puede dejar una
  reseña y editarla después.
  Al terminar un trabajo aceptado, el prestador puede marcarlo como hecho: el sistema
  descuenta los puntos al cliente y los suma al prestador en una sola operación.
- **Administrador:** gestionar el **catálogo de servicios** (crear, editar y borrar).

---

## 3. Mapa del proyecto

```
docker-compose.yml     Los dos contenedores: web (PHP + Apache) y db (MySQL)
Dockerfile             Cómo se construye el contenedor web
apache.conf            Apache: la raíz es public/ y todas las URLs pasan por index.php
composer.json          Dependencias PHP (Twig) y carga automática de clases

db/
  01-schema.sql        Las tablas (Anexo I del enunciado)
  02-seeds.sql         Datos de prueba

public/                Lo único que ve el navegador
  index.php            Punto de entrada: arranca la sesión y llama al Router
  css/styles.css       Estilos
  js/api.js            fetchJSON() y utilidades que usan todos los demás
  js/*.js              Un archivo por pantalla (login, registro, buscar, solicitudes, servicios-spa)
  uploads/avatars/     Fotos de perfil subidas

templates/             Las páginas (Twig): base.html.twig es el marco con el menú
src/
  Core/Router.php      La tabla de rutas: URL -> controlador
  Core/Auth.php        Sesión, usuario actual, permisos por rol y protección CSRF
  Controllers/         PaginaController (HTML) y los *ApiController (JSON)
  DAO/                 Las consultas SQL: lo único que habla con MySQL
  Models/              Usuario, Servicio, Categoria: los datos como objetos
```

**Regla de oro:** el controlador decide *qué* hacer, el DAO sabe *cómo* se guarda
y la plantilla sabe *cómo* se ve. Nadie salta esas capas.

---

## 4. Cómo viaja una petición

Ejemplo: **Laura pulsa «Solicitar» en un servicio de Carlos.**

1. `buscar.js` hace `fetchJSON('/api/solicitudes', { method: 'POST', ... })`.
   `api.js` añade la cabecera `X-CSRF-Token`.
2. Apache manda la URL a `public/index.php`, que arranca la sesión y llama a `Router`.
3. `Router` busca en `RUTAS` la línea `POST /api/solicitudes` → `SolicitudApiController::store`.
   Antes comprueba el token CSRF.
4. `store()` pide el usuario con `Auth::requireRole('PROFESIONAL')`, valida los datos
   (horas, fecha, saldo suficiente, que no sea su propia oferta) y calcula los puntos
   **en el servidor**, nunca fiándose del cliente.
5. `SolicitudDAO::create()` hace el `INSERT` con consultas preparadas.
6. El controlador responde JSON `201` y `buscar.js` muestra el mensaje en pantalla.

Las páginas siguen el mismo camino pero acaban en `PaginaController`, que pinta una
plantilla Twig; los datos de la pantalla los pide después el JavaScript con `fetch`.

---

## 5. Cómo añadir algo

**Una pantalla nueva:**
1. Línea en `Router::RUTAS`: `['GET', '/mi-pagina', PaginaController::class, 'miPagina']`.
2. Método `miPagina()` en `PaginaController` (comprobar quién puede verla y llamar a `render`).
3. Plantilla `templates/mi_pagina.html.twig` y, si carga datos, `public/js/mi-pagina.js`.

**Un endpoint nuevo:**
1. Línea en `Router::RUTAS`: `['GET', '/api/cosas/{id}', CosaApiController::class, 'show']`.
2. Método `show(int $id)` en el controlador: empieza con `Auth::requireLogin()` o
   `Auth::requireRole(...)`, valida, llama al DAO y responde con `jsonResponse(...)`.
3. La consulta SQL va en un método del DAO, nunca en el controlador.

---

## 6. Seguridad

- Contraseñas con `password_hash` (bcrypt); nunca se guardan ni se devuelven en claro.
- Sesión con cookie `HttpOnly` y `SameSite=Lax`, e id nuevo al iniciar sesión.
- Protección CSRF: toda petición que modifica datos lleva un token de la sesión.
- Roles: la API comprueba el rol en el servidor (`requireLogin` / `requireRole`); ocultar
  un botón en la pantalla no es una medida de seguridad.
- Todas las consultas SQL son preparadas (PDO).
- Los datos que decide el servidor no se aceptan del cliente: rol, puntos de bienvenida,
  puntos de cada solicitud y quién es el usuario.
- Las fotos se validan por su contenido real (no por la extensión), con tamaño máximo,
  y se guardan con un nombre aleatorio.
- Las bajas de usuarios (`activo = 0`) cierran la sesión al instante y el usuario deja
  de salir en el buscador.

---

## 7. Base de datos

Las seis tablas del Anexo I (`usuarios`, `categorias`, `servicios`,
`ofertas_profesionales`, `solicitudes_trueque`, `valoraciones`) están en `db/01-schema.sql`.
Se han añadido dos columnas a `usuarios`:

- `activo`: borrado lógico para dar de baja a un usuario sin perder su historial
  (con `ON DELETE CASCADE`, borrarlo arrastraría sus trueques).
- `foto`: ruta de la foto de perfil (opcional).
