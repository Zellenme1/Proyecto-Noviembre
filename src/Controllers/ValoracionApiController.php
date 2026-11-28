<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
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
        // Devuelve la media y todas las reseñas del profesional.
        $profesional = $this->usuarioDAO->getById($idProfesional);

        if ($profesional === null || !$profesional->isActivo() || $profesional->getRol() !== Usuario::ROL_PROFESIONAL) {
            $this->jsonResponse(['error' => 'Profesional no encontrado'], 404);
        }

        $this->jsonResponse([
            'resumen' => $this->valoracionDAO->resumenDeProfesional($idProfesional),
            'resenas' => $this->valoracionDAO->deProfesional($idProfesional),
        ]);
    }

    public function store(int $idSolicitud): void
    {
        $this->guardarParaSolicitud($idSolicitud, false);
    }

    public function update(int $idSolicitud): void
    {
        $this->guardarParaSolicitud($idSolicitud, true);
    }

    private function guardarParaSolicitud(int $idSolicitud, bool $editar): void
    {
        $usuario = Auth::requireRole(Usuario::ROL_PROFESIONAL);
        $in = $this->readJsonBody();
        $errores = [];

        $puntuacion = filter_var($in['puntuacion'] ?? null, FILTER_VALIDATE_INT);
        if ($puntuacion === false || $puntuacion < 1 || $puntuacion > 5) {
            $errores['puntuacion'] = 'Elige una puntuación entre 1 y 5';
        }

        $comentarioEntrada = $in['comentario'] ?? null;
        if ($comentarioEntrada !== null && !is_string($comentarioEntrada)) {
            $errores['comentario'] = 'El comentario no es válido';
        } elseif (is_string($comentarioEntrada) && mb_strlen(trim($comentarioEntrada)) > 2000) {
            $errores['comentario'] = 'El comentario no puede superar los 2000 caracteres';
        }

        if ($errores) {
            $this->jsonResponse(['error' => 'Datos no válidos', 'errores' => $errores], 422);
        }

        $comentario = is_string($comentarioEntrada) ? trim($comentarioEntrada) : '';
        $comentario = $comentario === '' ? null : $comentario;
        $resultado = $this->valoracionDAO->guardarParaSolicitud(
            $idSolicitud,
            (int)$usuario->getId(),
            $puntuacion,
            $comentario,
            $editar
        );

        if ($resultado === 'no_encontrada') {
            $this->jsonResponse(['error' => 'Solicitud no encontrada'], 404);
        }
        if ($resultado === 'no_completada') {
            $this->jsonResponse(['error' => 'Solo puedes valorar un servicio completado'], 409);
        }
        if ($resultado === 'ya_existe') {
            $this->jsonResponse(['error' => 'Ya existe una reseña para este servicio; puedes editarla'], 409);
        }
        if ($resultado === 'no_existe') {
            $this->jsonResponse(['error' => 'No hay una reseña para editar'], 404);
        }

        $this->jsonResponse(
            ['message' => $editar ? 'Reseña actualizada' : 'Reseña publicada'],
            $editar ? 200 : 201
        );
    }
}