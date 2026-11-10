<?php
declare(strict_types=1);

namespace App\Controllers;

use App\DAO\CategoriaDAO;
use App\DAO\ServicioDAO;
use App\Models\Servicio;

/**
 * CRUD del catálogo global de servicios.
 * TODO (fase de autenticación): restringir store/update/destroy al rol ADMIN.
 */
class ServicioApiController extends ApiController
{
    private ServicioDAO $servicioDAO;
    private CategoriaDAO $categoriaDAO;

    public function __construct()
    {
        $this->servicioDAO = new ServicioDAO();
        $this->categoriaDAO = new CategoriaDAO();
    }

    public function index(): void
    {
        if (isset($_GET['id_categoria'])) {
            $servicios = $this->servicioDAO->getByCategoria((int)$_GET['id_categoria']);
        } else {
            $servicios = $this->servicioDAO->getAll();
        }
        $this->jsonResponse($servicios);
    }

    public function show(int $id): void
    {
        $servicio = $this->servicioDAO->getById($id);
        if ($servicio === null) {
            $this->jsonResponse(['error' => 'Servicio no encontrado'], 404);
        }
        $this->jsonResponse($servicio);
    }

    public function store(): void
    {
        ['errores' => $errores, 'datos' => $d] = $this->validar($this->readJsonBody());
        if ($errores) {
            $this->jsonResponse(['error' => 'Datos no válidos', 'errores' => $errores], 422);
        }

        $id = $this->servicioDAO->create(
            new Servicio(null, $d['id_categoria'], $d['nombre'], $d['descripcion'], $d['puntos'])
        );
        $this->jsonResponse($this->servicioDAO->getById($id), 201);
    }

    public function update(int $id): void
    {
        if ($this->servicioDAO->getById($id) === null) {
            $this->jsonResponse(['error' => 'Servicio no encontrado'], 404);
        }

        ['errores' => $errores, 'datos' => $d] = $this->validar($this->readJsonBody());
        if ($errores) {
            $this->jsonResponse(['error' => 'Datos no válidos', 'errores' => $errores], 422);
        }

        $this->servicioDAO->update(
            new Servicio($id, $d['id_categoria'], $d['nombre'], $d['descripcion'], $d['puntos'])
        );
        $this->jsonResponse($this->servicioDAO->getById($id));
    }

    public function destroy(int $id): void
    {
        if ($this->servicioDAO->getById($id) === null) {
            $this->jsonResponse(['error' => 'Servicio no encontrado'], 404);
        }

        // La BD tiene ON DELETE CASCADE: borrar un servicio con ofertas arrastraría
        // también las solicitudes y el historial de puntos. Se bloquea expresamente.
        $ofertas = $this->servicioDAO->countOfertas($id);
        if ($ofertas > 0) {
            $this->jsonResponse([
                'error' => "No se puede eliminar: {$ofertas} profesional(es) ofrecen este servicio",
            ], 409);
        }

        $this->servicioDAO->delete($id);
        $this->jsonResponse(['message' => 'Servicio eliminado correctamente']);
    }

    /**
     * Valida el JSON de entrada (PUT y POST llevan los mismos campos).
     *
     * @return array{errores: array<string,string>, datos: array}
     */
    private function validar(array $in): array
    {
        $errores = [];

        $nombre = is_string($in['nombre'] ?? null) ? trim($in['nombre']) : '';
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            $errores['nombre'] = 'El nombre es obligatorio (máximo 150 caracteres)';
        }

        $descripcion = is_string($in['descripcion'] ?? null) ? trim($in['descripcion']) : '';

        $puntos = filter_var($in['puntos_hora_referencia'] ?? null, FILTER_VALIDATE_INT);
        if ($puntos === false || $puntos < 1 || $puntos > 500) {
            $errores['puntos_hora_referencia'] = 'Los puntos por hora deben ser un entero entre 1 y 500';
        }

        $idCategoria = filter_var($in['id_categoria'] ?? null, FILTER_VALIDATE_INT);
        if ($idCategoria === false || $this->categoriaDAO->getById($idCategoria) === null) {
            $errores['id_categoria'] = 'Selecciona una categoría existente';
        }

        return [
            'errores' => $errores,
            'datos' => [
                'nombre' => $nombre,
                'descripcion' => $descripcion === '' ? null : $descripcion,
                'puntos' => $puntos,
                'id_categoria' => $idCategoria,
            ],
        ];
    }
}
