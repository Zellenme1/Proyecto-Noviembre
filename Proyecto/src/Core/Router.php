<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\CategoriaApiController;
use App\Controllers\ServicioApiController;
use App\Controllers\ServicioWebController;

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

            case 'servicios-spa':
                if ($method === 'GET') {
                    (new ServicioWebController())->spa();
                } else {
                    $this->responderError(405, 'Método no permitido para esta vista');
                }
                break;

            default:
                $this->responderError(404, 'Ruta no encontrada');
        }
    }

    private function despacharApi(string $method, array $segments): void
    {
        $recurso = $segments[1] ?? null;
        $id = isset($segments[2]) ? (int)$segments[2] : null;

        switch ($recurso) {
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

            case 'categorias':
                if ($method !== 'GET') {
                    $this->responderError(405, 'Método HTTP no soportado');
                }
                (new CategoriaApiController())->index();
                break;

            default:
                $this->responderError(404, 'Recurso API no encontrado');
        }
    }

    private function responderError(int $code, string $message): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
