<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Base de los controladores que devuelven HTML con Twig.
 * Todas las plantillas reciben `usuario` (o null) y `csrf_token`.
 */
abstract class WebController
{
    protected Environment $twig;

    public function __construct()
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
        $this->twig->addGlobal('csrf_token', Csrf::token());
        $this->twig->addGlobal('usuario', Auth::user());
    }

    protected function render(string $plantilla, array $contexto = [], int $status = 200): void
    {
        http_response_code($status);
        echo $this->twig->render($plantilla, $contexto);
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
