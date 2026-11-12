<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;

date_default_timezone_set('Europe/Madrid');

Session::start();
(new Router())->dispatch();
