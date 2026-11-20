<?php
declare(strict_types=1);

namespace App\Controllers;

use App\DAO\OfertaDAO;

/**
 * GET /api/ofertas?id_categoria=..&q=..
 * Buscador público: cualquiera puede ver qué servicios se ofrecen.
 * Para solicitar uno hace falta iniciar sesión (POST /api/solicitudes).
 */
class OfertaApiController extends ApiController
{
    private OfertaDAO $ofertaDAO;

    public function __construct()
    {
        $this->ofertaDAO = new OfertaDAO();
    }

    public function index(): void
    {
        $idCategoria = filter_var($_GET['id_categoria'] ?? null, FILTER_VALIDATE_INT);
        $idCategoria = ($idCategoria === false || $idCategoria < 1) ? null : $idCategoria;

        $texto = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 100) : '';

        $this->jsonResponse($this->ofertaDAO->buscar($idCategoria, $texto));
    }
}
