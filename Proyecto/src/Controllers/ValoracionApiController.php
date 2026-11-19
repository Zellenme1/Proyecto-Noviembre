<?php
declare(strict_types=1);

namespace App\Controllers;

use App\DAO\UsuarioDAO;
use App\DAO\ValoracionDAO;
use App\Models\Usuario;

class ValoracionApiController extends ApiController
{
    private ValoracionDAO $valoracionDAO;
    private UsuarioDAO $usuarioDAO;

    public function __construct()
    {
        $this->valoracionDAO = new ValoracionDAO();
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function index(int $idProfesional): void
    {
        $profesional = $this->usuarioDAO->getById($idProfesional);

        if ($profesional === null || !$profesional->isActivo() || $profesional->getRol() !== Usuario::ROL_PROFESIONAL) {
            $this->jsonResponse(['error' => 'Profesional no encontrado'], 404);
        }
    
        $this->jsonResponse([
            'resumen' => $this->valoracionDAO->resumenDeProfesional($idProfesional),
            'resenas' => $this->valoracionDAO->deProfesional($idProfesional),
        ]);
    }
}