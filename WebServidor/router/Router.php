<?php
require_once __DIR__ . '/Controller.php';

class Router
{
    public function dispatch()
    {
        $opcion = $_GET['menu'] ?? '';
        $controller = new Controller();

        switch ($opcion) {
            case 'pintalaa':
                $controller->pintalaa();
                break;
            case 'pintalab':
                $controller->pintalab();
                break;
        }
    }
}