<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Usuario;

/**
 * Páginas generales: inicio y login.
 */
class PaginaController extends WebController
{
    public function inicio(): void
    {
        $usuario = Auth::user();
        if ($usuario === null) {
            $this->redirect('/login');
        }
        if ($usuario->getRol() === Usuario::ROL_ADMIN) {
            $this->redirect('/servicios-spa');
        }
        $this->render('inicio.html.twig', ['titulo_pagina' => 'Inicio']);
    }

    public function login(): void
    {
        if (Auth::user() !== null) {
            $this->redirect('/');
        }
        $this->render('login.html.twig', ['titulo_pagina' => 'Iniciar sesión']);
    }
}
