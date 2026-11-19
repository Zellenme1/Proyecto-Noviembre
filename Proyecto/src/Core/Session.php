<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Inicia la sesión PHP con cookies seguras.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');   // rechaza ids de sesión no generados por el servidor
        ini_set('session.use_only_cookies', '1');  // nunca aceptar el id por URL

        session_name('TRUEQUES_SESSID');
        session_set_cookie_params([
            'lifetime' => 0,                              // cookie de sesión
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']),        // solo HTTPS cuando lo haya
            'httponly' => true,                           // inaccesible desde JavaScript
            'samesite' => 'Lax',                          // no se envía en peticiones cruzadas POST
        ]);
        session_start();
    }
}
