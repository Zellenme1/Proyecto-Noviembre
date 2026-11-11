<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\DAO\CategoriaDAO;

/**
 * Por ahora solo lectura (para rellenar el desplegable de categorías).
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
        Auth::requireLogin();
        $this->jsonResponse($this->categoriaDAO->getAll());
    }
}
