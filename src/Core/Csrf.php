<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Protección CSRF con token de sesión.
 * El token se inyecta en <meta name="csrf-token"> y el JS lo envía en la
 * cabecera X-CSRF-Token en toda petición que modifica datos (POST/PUT/DELETE).
 */
final class Csrf
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        // Usa el mismo token durante la sesión para comprobar envíos.
        if (empty($_SESSION[self::KEY]) || !is_string($_SESSION[self::KEY])) {
            return self::rotate();
        }
        return $_SESSION[self::KEY];
    }

    /** Genera un token nuevo (se hace al iniciar sesión). */
    public static function rotate(): string
    {
        return $_SESSION[self::KEY] = bin2hex(random_bytes(32));
    }

    public static function isValid(): bool
    {
        $enviado = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return is_string($enviado) && $enviado !== '' && hash_equals(self::token(), $enviado);
    }
}
