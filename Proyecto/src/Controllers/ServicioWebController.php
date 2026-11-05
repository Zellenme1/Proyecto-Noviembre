<?php
namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ServicioWebController
{
    private Environment $twig;

    public function __construct(){
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $this->twig = new Environment($loader);
    }

    public function spa(): void {
        echo $this->twig->render('servicios_spa.html.twig', [
            'titulo_pagina' => 'Gestión de Servicios (SPA)'
        ]);
    }
}