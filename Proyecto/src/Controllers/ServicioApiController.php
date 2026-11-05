<?php
namespace App\Controllers;

use App\DAO\ServicioDAO;
use App\Models\Servicio;

class ServicioApiController {
    private ServicioDAO $servicioDAO;

    public function __construct() {
        $this->servicioDAO = new ServicioDAO();
    }

    public function index() {
        if (isset($_GET['id_categoria'])) {
            $servicios = $this->servicioDAO->getServiciosByCategoria($_GET['id_categoria']);
        } else {
            $servicios = $this->servicioDAO->getAllServicios();
        }
        $this->jsonResponse($servicios, 200);
    }

    public function show($id) {
        $servicio = $this->servicioDAO->getServicioById($id);
        if ($servicio) {
            $this->jsonResponse($servicio, 200);
        } else {
            $this->jsonResponse(['error' => 'Servicio no encontrado'], 404);
        }
    }

    public function store(): void {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['nombre']) || !isset($input['descripcion']) || !isset($input['id_usuario']) || !isset($input['id_categoria'])) {
            $this->jsonResponse(['error' => 'Datos incompletos'], 400);
            return;
        }

        $servicio = new Servicio(
        null,
        trim($input['nombre']),
        trim($input['descripcion']),
        (int)$input['id_usuario'],
        (int)$input['id_categoria']
        );

        if ($this->servicioDAO->create($servicio)) {
            $this->jsonResponse(['message' => 'Servicio creado exitosamente'], 201);
        } else {
            $this->jsonResponse(['error' => 'Error al crear el servicio'], 500);
        }
    }

    public function update(int $id): void {
        $servicioExistente = $this->servicioDAO->getServicioById($id);
        if (!$servicioExistente) {
            $this->jsonResponse(['error' => 'Servicio no encontrado'], 404);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $servicioActualizado = new Servicio(
            $id,
            isset($input['nombre']) ? trim($input['nombre']) : $servicioExistente->getNombre(),
            isset($input['descripcion']) ? trim($input['descripcion']) : $servicioExistente->getDescripcion(),
            isset($input['id_usuario']) ? (int)$input['id_usuario'] : $servicioExistente->getIdUsuario(),
            isset($input['id_categoria']) ? (int)$input['id_categoria'] : $servicioExistente->getIdCategoria()
        );

        if ($this->servicioDAO->update($servicioActualizado)) {
            $this->jsonResponse(['message' => 'Servicio actualizado exitosamente'], 200);
        } else {
            $this->jsonResponse(['error' => 'Error al actualizar el servicio'], 500);
        }
    }

    public function delete(int $id): void {;
        if (!$this->servicioDAO->getById($id)) {
            $this->jsonResponse(['error' => 'Servicio no encontrado'], 404);
            return;
        }

        if ($this->servicioDAO->delete($id)) {
            $this->jsonResponse(['message' => 'Servicio eliminado exitosamente'], 200);
        } else {
            $this->jsonResponse(['error' => 'Error al eliminar el servicio'], 500);
        }
    }

    private function jsonResponse(mixed $data, int $statusCode): void {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}