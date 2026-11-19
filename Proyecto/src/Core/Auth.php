<?php
declare(strict_types=1);

namespace App\Core;

use App\DAO\UsuarioDAO;
use App\Models\Usuario;

/**
 * Todo lo que tiene que ver con "quién eres y qué puedes hacer":
 *   1. La sesión PHP.
 *   2. El usuario que ha iniciado sesión (login, logout, user).
 *   3. Los permisos por rol en la API (requireLogin, requireRole).
 *   4. La protección CSRF (csrfToken, csrfIsValid).
 *
 * En la sesión solo se guarda el id del usuario. El rol, el saldo y si sigue
 * dado de alta se leen siempre de la base de datos, así una baja surte efecto
 * al instante.
 */
final class Auth
{
    private const CLAVE_USUARIO = 'id_usuario';
    private const CLAVE_CSRF = '_csrf';

    private static ?Usuario $usuario = null;
    private static bool $cargado = false;

    // -------------------------------------------------------------------------
    // 1. Sesión
    // -------------------------------------------------------------------------

    /** Se llama una vez, al principio de public/index.php. */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');   // no acepta ids de sesión inventados
        ini_set('session.use_only_cookies', '1');  // el id nunca viaja por la URL

        session_name('TRUEQUES_SESSID');
        session_set_cookie_params([
            'lifetime' => 0,                          // se borra al cerrar el navegador
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']),    // solo por HTTPS cuando lo haya
            'httponly' => true,                       // JavaScript no puede leerla
            'samesite' => 'Lax',                      // no se envía desde otras webs
        ]);
        session_start();
    }

    // -------------------------------------------------------------------------
    // 2. Usuario actual
    // -------------------------------------------------------------------------

    public static function login(Usuario $usuario): void
    {
        session_regenerate_id(true);   // id de sesión nuevo: evita la fijación de sesión
        $_SESSION[self::CLAVE_USUARIO] = $usuario->getId();
        self::nuevoTokenCsrf();
        self::$usuario = $usuario;
        self::$cargado = true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $p['path'],
                'domain' => $p['domain'],
                'secure' => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?: 'Lax',
            ]);
        }
        session_destroy();
        self::$usuario = null;
        self::$cargado = true;
    }

    /** El usuario con sesión iniciada, o null si es un visitante. */
    public static function user(): ?Usuario
    {
        if (self::$cargado) {
            return self::$usuario;
        }
        self::$cargado = true;

        $id = $_SESSION[self::CLAVE_USUARIO] ?? null;
        if (!is_int($id)) {
            return null;
        }

        $usuario = (new UsuarioDAO())->getById($id);
        if ($usuario === null || !$usuario->isActivo()) {
            unset($_SESSION[self::CLAVE_USUARIO]);
            return null;
        }
        return self::$usuario = $usuario;
    }

    // -------------------------------------------------------------------------
    // 3. Permisos (para los endpoints de la API)
    // -------------------------------------------------------------------------

    /** Responde 401 si no hay sesión. */
    public static function requireLogin(): Usuario
    {
        $usuario = self::user();
        if ($usuario === null) {
            self::jsonError(401, 'Debes iniciar sesión');
        }
        return $usuario;
    }

    /** Responde 401 si no hay sesión y 403 si el rol no es el pedido. */
    public static function requireRole(string $rol): Usuario
    {
        $usuario = self::requireLogin();
        if ($usuario->getRol() !== $rol) {
            self::jsonError(403, 'No tienes permisos para realizar esta acción');
        }
        return $usuario;
    }

    // -------------------------------------------------------------------------
    // 4. CSRF
    //    El token va en <meta name="csrf-token"> y el JS lo manda en la cabecera
    //    X-CSRF-Token en cada petición que modifica datos (POST, PUT, DELETE).
    //    Así una web ajena no puede hacer peticiones en nombre del usuario.
    // -------------------------------------------------------------------------

    public static function csrfToken(): string
    {
        if (empty($_SESSION[self::CLAVE_CSRF]) || !is_string($_SESSION[self::CLAVE_CSRF])) {
            return self::nuevoTokenCsrf();
        }
        return $_SESSION[self::CLAVE_CSRF];
    }

    public static function csrfIsValid(): bool
    {
        $enviado = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return is_string($enviado) && $enviado !== '' && hash_equals(self::csrfToken(), $enviado);
    }

    private static function nuevoTokenCsrf(): string
    {
        return $_SESSION[self::CLAVE_CSRF] = bin2hex(random_bytes(32));
    }

    // -------------------------------------------------------------------------

    private static function jsonError(int $codigo, string $mensaje): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
