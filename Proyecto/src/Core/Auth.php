<?php
declare(strict_types=1);

namespace App\Core;

use App\DAO\UsuarioDAO;
use App\Models\Usuario;

/**
 * Usuario autenticado y control de acceso por rol.
 * En la sesión solo se guarda el id: rol, saldo y estado se leen siempre de la
 * base de datos, así una baja o un cambio de rol surte efecto al instante.
 */
final class Auth
{
    private const KEY = 'id_usuario';

    private static ?Usuario $cache = null;
    private static bool $cargado = false;

    public static function login(Usuario $usuario): void
    {
        session_regenerate_id(true);   // evita la fijación de sesión
        $_SESSION[self::KEY] = $usuario->getId();
        Csrf::rotate();
        self::$cache = $usuario;
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
        self::$cache = null;
        self::$cargado = true;
    }

    public static function user(): ?Usuario
    {
        if (self::$cargado) {
            return self::$cache;
        }
        self::$cargado = true;

        $id = $_SESSION[self::KEY] ?? null;
        if (!is_int($id)) {
            return null;
        }

        $usuario = (new UsuarioDAO())->getById($id);
        if ($usuario === null || !$usuario->isActivo()) {
            unset($_SESSION[self::KEY]);
            return null;
        }
        return self::$cache = $usuario;
    }

    /** Para endpoints de la API: responde 401 si no hay sesión. */
    public static function requireLogin(): Usuario
    {
        $usuario = self::user();
        if ($usuario === null) {
            self::jsonError(401, 'Debes iniciar sesión');
        }
        return $usuario;
    }

    /** Para endpoints de la API: responde 401 sin sesión y 403 si el rol no coincide. */
    public static function requireRole(string $rol): Usuario
    {
        $usuario = self::requireLogin();
        if ($usuario->getRol() !== $rol) {
            self::jsonError(403, 'No tienes permisos para realizar esta acción');
        }
        return $usuario;
    }

    private static function jsonError(int $codigo, string $mensaje): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
