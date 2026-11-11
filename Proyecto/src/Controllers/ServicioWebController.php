<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Usuario;

/**
 * Página del catálogo de servicios: solo accesible para el ADMIN.
 */
class ServicioWebController extends WebController
{
    public function spa(): void
    {
        $usuario = Auth::user();
        if ($usuario === null) {
            $this->redirect('/login');
        }
        if ($usuario->getRol() !== Usuario::ROL_ADMIN) {
            $this->render('error.html.twig', [
                'titulo_pagina' => 'Acceso denegado',
                'codigo' => 403,
                'mensaje' => 'Esta zona es solo para el administrador.',
            ], 403);
            return;
        }
        $this->render('servicios_spa.html.twig', ['titulo_pagina' => 'Catálogo de servicios']);
    }
}
