<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\AuthApiController;
use App\Controllers\CategoriaApiController;
use App\Controllers\OfertaApiController;
use App\Controllers\PaginaController;
use App\Controllers\PerfilApiController;
use App\Controllers\ServicioApiController;
use App\Controllers\ServicioWebController;
use App\Controllers\SolicitudApiController;

class Router
{
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Ejemplo: '/api/servicios/5' -> ['api', 'servicios', '5']
        $segments = array_values(array_filter(explode('/', $uri)));
        $primerSegmento = $segments[0] ?? '';

        switch ($primerSegmento) {
            case 'api':
                $this->despacharApi($method, $segments);
                break;

            case '':
                $this->soloGet($method);
                (new PaginaController())->inicio();
                break;

            case 'login':
                $this->soloGet($method);
                (new PaginaController())->login();
                break;

            case 'registro':
                $this->soloGet($method);
                (new PaginaController())->registro();
                break;

            case 'buscar':
                $this->soloGet($method);
                (new PaginaController())->buscar();
                break;

            case 'solicitudes':
                $this->soloGet($method);
                (new PaginaController())->solicitudes();
                break;

            case 'servicios-spa':
                $this->soloGet($method);
                (new ServicioWebController())->spa();
                break;

            default:
                $this->responderError(404, 'Ruta no encontrada');
        }
    }

    private function despacharApi(string $method, array $segments): void
    {
        // CSRF: toda petición que modifica datos debe llevar el token de la sesión.
        if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true) && !Csrf::isValid()) {
            $this->responderError(403, 'Token CSRF no válido o ausente');
        }

        $recurso = $segments[1] ?? null;
        $id = isset($segments[2]) ? (int)$segments[2] : null;

        switch ($recurso) {
            case 'login':
                $this->soloMetodo($method, 'POST');
                (new AuthApiController())->login();
                break;

            case 'logout':
                $this->soloMetodo($method, 'POST');
                (new AuthApiController())->logout();
                break;

            case 'registro':
                $this->soloMetodo($method, 'POST');
                (new AuthApiController())->registro();
                break;

            case 'me':
                $this->soloMetodo($method, 'GET');
                (new AuthApiController())->me();
                break;

            case 'servicios':
                $controller = new ServicioApiController();

                switch ($method) {
                    case 'GET':
                        $id !== null ? $controller->show($id) : $controller->index();
                        break;
                    case 'POST':
                        $controller->store();
                        break;
                    case 'PUT':
                        $id !== null
                            ? $controller->update($id)
                            : $this->responderError(400, 'Falta el ID en la URI para actualizar');
                        break;
                    case 'DELETE':
                        $id !== null
                            ? $controller->destroy($id)
                            : $this->responderError(400, 'Falta el ID en la URI para eliminar');
                        break;
                    default:
                        $this->responderError(405, 'Método HTTP no soportado');
                }
                break;

            case 'foto':
                $this->soloMetodo($method, 'POST');
                (new PerfilApiController())->subirFoto();
                break;

            case 'categorias':
                $this->soloMetodo($method, 'GET');
                (new CategoriaApiController())->index();
                break;

            case 'ofertas':
                $this->soloMetodo($method, 'GET');
                (new OfertaApiController())->index();
                break;

            case 'solicitudes':
                $controller = new SolicitudApiController();

                switch ($method) {
                    case 'GET':
                        $controller->index();
                        break;
                    case 'POST':
                        $controller->store();
                        break;
                    case 'PUT':
                        $id !== null && $id > 0
                            ? $controller->update($id)
                            : $this->responderError(400, 'Falta el ID en la URI para actualizar');
                        break;
                    default:
                        $this->responderError(405, 'Método HTTP no soportado');
                }
                break;

            default:
                $this->responderError(404, 'Recurso API no encontrado');
        }
    }

    private function soloGet(string $method): void
    {
        $this->soloMetodo($method, 'GET');
    }

    private function soloMetodo(string $method, string $permitido): void
    {
        if ($method !== $permitido) {
            $this->responderError(405, 'Método HTTP no soportado');
        }
    }

    private function responderError(int $code, string $message): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
