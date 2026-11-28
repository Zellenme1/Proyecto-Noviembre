<?php
declare(strict_types=1);

namespace App\Controllers;

use App\DAO\CategoriaDAO;

/**
 * Por ahora solo lectura, y pública: rellena el filtro del buscador de servicios.
 * El CRUD de categorías del admin se añadirá en su fase.
 */
class CategoriaApiController extends ApiController
{
    private CategoriaDAO $categoriaDAO;

    public function __construct()
    {
        $this->categoriaDAO = new CategoriaDAO();
    }

    public function index(): void
    {
        // Se obtienen todas las categorías y se devuelven como JSON.
        $this->jsonResponse($this->categoriaDAO->getAll());
    }
}
