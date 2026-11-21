<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\AuthApiController;
use App\Controllers\CategoriaApiController;
use App\Controllers\OfertaApiController;
use App\Controllers\PaginaController;
use App\Controllers\PerfilApiController;
use App\Controllers\ServicioApiController;
use App\Controllers\SolicitudApiController;
use App\Controllers\ValoracionApiController;

/**
 * El Router mira qué ha pedido el navegador (método + URL) y llama al método
 * del controlador que toca. Todas las rutas de la aplicación están en RUTAS:
 * para añadir una página o un endpoint solo hay que añadir una línea.
 */
class Router
{
    /**
     * [método HTTP, URL, controlador, método del controlador]
     * "{id}" es un número de la URL que se pasa al método del controlador.
     */
    private const RUTAS = [
        // ---- Páginas (devuelven HTML)
        ['GET', '/',              PaginaController::class, 'inicio'],
        ['GET', '/login',         PaginaController::class, 'login'],
        ['GET', '/registro',      PaginaController::class, 'registro'],
        ['GET', '/buscar',        PaginaController::class, 'buscar'],
        ['GET', '/profesionales/{id}', PaginaController::class, 'perfilPublico'],
        ['GET', '/solicitudes',   PaginaController::class, 'solicitudes'],
        ['GET', '/servicios-spa', PaginaController::class, 'catalogo'],
        ['GET', '/perfil', PaginaController::class, 'Perfil'],

        // ---- API de cuenta (devuelven JSON)
        ['POST', '/api/login',    AuthApiController::class, 'login'],
        ['POST', '/api/logout',   AuthApiController::class, 'logout'],
        ['POST', '/api/registro', AuthApiController::class, 'registro'],

        ['POST', '/api/foto',     PerfilApiController::class, 'subirFoto'],
        ['POST', '/api/curriculum', PerfilApiController::class, 'subirCurriculum'],
        ['DELETE', '/api/perfil', PerfilApiController::class, 'eliminarCuenta'],

        // ---- API pública: filtro y buscador
        ['GET', '/api/categorias', CategoriaApiController::class, 'index'],
        ['GET', '/api/ofertas',    OfertaApiController::class,    'index'],
        ['GET', '/api/profesionales/{id}/resenas', ValoracionApiController::class, 'index'],

        // ---- API del catálogo de servicios (escribir: solo ADMIN)
        ['GET',    '/api/servicios',      ServicioApiController::class, 'index'],
        ['GET',    '/api/servicios/{id}', ServicioApiController::class, 'show'],
        ['POST',   '/api/servicios',      ServicioApiController::class, 'store'],
        ['PUT',    '/api/servicios/{id}', ServicioApiController::class, 'update'],
        ['DELETE', '/api/servicios/{id}', ServicioApiController::class, 'destroy'],

        // ---- API de solicitudes (solo PROFESIONAL)
        ['GET',  '/api/solicitudes',      SolicitudApiController::class, 'index'],
        ['GET',  '/api/puntos-disponibles', SolicitudApiController::class, 'puntosDisponibles'],
        ['POST', '/api/solicitudes',      SolicitudApiController::class, 'store'],
        ['PUT',  '/api/solicitudes/{id}', SolicitudApiController::class, 'update'],

        // ---- Reseñas de servicios completados (solo quien solicitó el trabajo)
        ['POST', '/api/solicitudes/{id}/valoracion', ValoracionApiController::class, 'store'],
        ['PUT',  '/api/solicitudes/{id}/valoracion', ValoracionApiController::class, 'update'],
    ];

    public function dispatch(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $url = rtrim((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

        $urlConocida = false;

        foreach (self::RUTAS as [$metodoRuta, $patron, $controlador, $accion]) {
            // "/api/servicios/{id}" se convierte en la expresión "#^/api/servicios/(\d+)$#"
            $regex = '#^' . str_replace('{id}', '(\d+)', $patron) . '$#';
            if (!preg_match($regex, $url, $coincidencias)) {
                continue;
            }

            $urlConocida = true;
            if ($metodoRuta !== $metodo) {
                continue; // la URL existe, pero no para este método
            }

            // CSRF: toda petición que modifica datos debe llevar el token de la sesión.
            if ($metodo !== 'GET' && !Auth::csrfIsValid()) {
                $this->error($url, 403, 'Token CSRF no válido o ausente');
            }

            $parametros = array_map('intval', array_slice($coincidencias, 1));
            (new $controlador())->$accion(...$parametros);
            return;
        }

        if ($urlConocida) {
            $this->error($url, 405, 'Método HTTP no soportado');
        }
        $this->error($url, 404, 'Ruta no encontrada');
    }

    /** La API responde con JSON; las páginas, con una pantalla de error. */
    private function error(string $url, int $codigo, string $mensaje): never
    {
        if (!str_starts_with($url, '/api/')) {
            (new PaginaController())->error($codigo, $mensaje);
        }

        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
