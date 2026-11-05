<?php
class Auth
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function requerir(): void
    {
        self::iniciar();
        if (empty($_SESSION["usuario_id"])) {
            header('Location: login.php');
            exit;
        }
    }

    public static function estaLogueado(): bool
    {
        self::iniciar();
        return !empty($_SESSION['usuario_id']);
    }

    public static function entrar(Usuario $u): void
    {
        self::iniciar();
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $u->id;
        $_SESSION['usuario_nombre'] = $u->nombre;
    }

    public static function nombre(): string
    {
        self::iniciar();
        return $_SESSION['usuario_nombre'] ?? '';
    }

    public static function salir(): void
    {
        self::iniciar();
        $_SESSION = [];
        session_destroy();
    }
}   
