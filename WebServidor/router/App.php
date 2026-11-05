<?php
require_once __DIR__ . '/Router.php';

class App
{
    public static function main()
    {
        $router = new Router();
        $router->dispatch();
    }
}