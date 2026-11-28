<?php
// Carga el programa y envía la petición a la ruta correspondiente.
// Punto de entrada de la aplicación.
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Auth;
use App\Core\Router;

date_default_timezone_set('Europe/Madrid');

Auth::start();
(new Router())->dispatch();
