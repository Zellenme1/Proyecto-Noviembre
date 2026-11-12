<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Usuario;

/**
 * Páginas generales: inicio, buscador de servicios, registro, login y
 * "mis solicitudes". Inicio y buscador son públicos.
 */
class PaginaController extends WebController
{
    public function inicio(): void
    {
        $this->render('inicio.html.twig', ['titulo_pagina' => 'Inicio']);
    }

    public function buscar(): void
    {
        $this->render('buscar.html.twig', ['titulo_pagina' => 'Buscar servicio']);
    }

    public function login(): void
    {
        if (Auth::user() !== null) {
            $this->redirect('/');
        }
        $this->render('login.html.twig', ['titulo_pagina' => 'Iniciar sesión']);
    }

    public function registro(): void
    {
        if (Auth::user() !== null) {
            $this->redirect('/');
        }
        $this->render('registro.html.twig', ['titulo_pagina' => 'Crear cuenta']);
    }

    /** Solo para profesionales: el admin no envía ni recibe solicitudes. */
    public function solicitudes(): void
    {
        $usuario = Auth::user();
        if ($usuario === null) {
            $this->redirect('/login');
        }
        if ($usuario->getRol() === Usuario::ROL_ADMIN) {
            $this->redirect('/servicios-spa');
        }
        $this->render('solicitudes.html.twig', ['titulo_pagina' => 'Mis solicitudes']);
    }
}
