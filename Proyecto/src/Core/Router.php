<?php
namespace App\Core;

use App\Controllers\ServicioApiController;
use App\Controllers\ServicioWebController;

class Router {
    public function dispatch(): void{
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);



        $segments = array_values(array_filter(explode('/', $uri)));

        $primerSegmento = $segments[0] ?? null;

        switch ($primerSegmento) {
            case 'api':
                $this->handleApiRoutes($method, $segments);
                break;

            case 'servicios':
                if ($method === 'GET') {
                    (new ServicioWebController())->spa();
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
                break;

            default:
                $this->responderError(404, 'Ruta no encontrada');
                break;
        }
    }

    private function despacharApiRoutes(string $method, array $segments): void {
        $recurso = $segments[1] ?? null;
        $id = isset($segments[2]) ? (int)$segments[2] : null;

        switch ($recurso) {
            case 'servicios':
                $controller = new ServicioApiController();

                switch ($method) {
                    case 'GET':
                        if ($id !== null) {
                            $controller->show($id);
                        } else {
                            $controller->index();
                        }
                        break;

                    case 'POST':
                        $controller->store();
                        break;

                    case 'PUT':
                        if ($id !== null) {
                            $controller->update($id);
                        } else {
                            $this->responderError(400, 'ID de servicio requerido para actualizar');
                        }
                        break;

                    case 'DELETE':
                        if ($id !== null) {
                            $controller->destroy($id);
                        } else {
                            $this->responderError(400, 'ID de servicio requerido para eliminar');
                        }
                        break;

                    default:
                        $this->responderError(405, 'Método no permitido');
                        break;
                }
                break;

            default:
                $this->responderError(404, 'Recurso no encontrado');
                break;
        }
    }

    private function responderError(int $codigo, string $message): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message]);
    }
}