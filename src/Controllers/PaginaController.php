<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\DAO\CategoriaDAO;
use App\DAO\ServicioDAO;
use App\DAO\UsuarioDAO;
use App\Models\Usuario;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Todas las páginas HTML de la aplicación. Cada método comprueba quién puede
 * verla y pinta su plantilla de la carpeta templates/. Los datos (servicios,
 * ofertas, solicitudes...) los carga después el JavaScript con fetch().
 */
class PaginaController
{
    private Environment $twig;

    public function __construct()
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));

        // Disponibles en TODAS las plantillas
        $this->twig->addGlobal('usuario', Auth::user());          // null si es un visitante
        $this->twig->addGlobal('csrf_token', Auth::csrfToken());
    }

    // -------------------------------------------------------------------------
    // Públicas
    // -------------------------------------------------------------------------

    public function inicio(): void
    {
        $this->render('inicio.html.twig', 'Inicio');
    }

    public function buscar(): void
    {
        $this->render('buscar.html.twig', 'Buscar servicio');
    }

    public function perfilPublico(int $id): void
    {
        $profesional = (new UsuarioDAO())->getById($id);
        if (
            $profesional === null
            || !$profesional->isActivo()
            || $profesional->getRol() !== Usuario::ROL_PROFESIONAL
        ) {
            $this->error(404, 'Profesional no encontrado.');
        }

        $nombre = $profesional->getNombre() . ' ' . $profesional->getApellidos();
        $this->render('perfil_publico.html.twig', $nombre, ['profesional' => $profesional]);
    }

    /** Solo para visitantes: con la sesión iniciada no tiene sentido. */
    public function login(): void
    {
        $this->soloVisitantes();
        $this->render('login.html.twig', 'Iniciar sesión');
    }

    public function registro(): void
    {
        $this->soloVisitantes();
        $this->render('registro.html.twig', 'Crear cuenta', ['grupos' => $this->serviciosPorCategoria()]);
    }

    // -------------------------------------------------------------------------
    // Con sesión
    // -------------------------------------------------------------------------

    /** Solo PROFESIONAL: el admin no envía ni recibe solicitudes. */
    public function solicitudes(): void
    {
        $usuario = $this->exigirSesion();
        if ($usuario->getRol() === Usuario::ROL_ADMIN) {
            $this->redirect('/servicios-spa');
        }
        $this->render('solicitudes.html.twig', 'Mis solicitudes');
    }

    /** Solo ADMIN: gestión del catálogo de servicios. */
    public function catalogo(): void
    {
        $usuario = $this->exigirSesion();
        if ($usuario->getRol() !== Usuario::ROL_ADMIN) {
            $this->error(403, 'Esta zona es solo para el administrador.');
        }
        $this->render('servicios_spa.html.twig', 'Catálogo de servicios');
    }

    // -------------------------------------------------------------------------
    // Errores (el Router también la usa para 404 y 405)
    // -------------------------------------------------------------------------

    public function error(int $codigo, string $mensaje): never
    {
        $this->render('error.html.twig', 'Error ' . $codigo, ['codigo' => $codigo, 'mensaje' => $mensaje], $codigo);
        exit;
    }

    // -------------------------------------------------------------------------

    private function render(string $plantilla, string $titulo, array $datos = [], int $estado = 200): void
    {
        http_response_code($estado);
        echo $this->twig->render($plantilla, $datos + ['titulo_pagina' => $titulo]);
    }

    /** El catálogo agrupado por categoría, para elegir servicios al registrarse. */
    private function serviciosPorCategoria(): array
    {
        // Junta cada servicio dentro de su categoría para el registro.
        $grupos = [];
        foreach ((new CategoriaDAO())->getAll() as $categoria) {
            $grupos[$categoria->getId()] = ['categoria' => $categoria, 'servicios' => []];
        }
        foreach ((new ServicioDAO())->getAll() as $servicio) {
            $grupos[$servicio->getIdCategoria()]['servicios'][] = $servicio;
        }
        return array_filter($grupos, fn(array $grupo) => $grupo['servicios'] !== []);
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /** Devuelve el usuario; si no ha iniciado sesión, lo manda al login. */
    private function exigirSesion(): Usuario
    {
        $usuario = Auth::user();
        if ($usuario === null) {
            $this->redirect('/login');
        }
        return $usuario;
    }

    private function soloVisitantes(): void
    {
        if (Auth::user() !== null) {
            $this->redirect('/');
        }
    }

    /** Cualquier usuario con sesion: sus datos personales. **/
    public function perfil(): void
    {
        $this->exigirSesion();
        $this->render('perfil.html.twig', 'Mi perfil');
    }
}
