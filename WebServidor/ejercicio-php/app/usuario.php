<?php
require_once 'bd.php';

class Usuario
{
    public function __construct(
        public ?int $id = null,
        public string $nombre = '',
        public string $passwordHash = '' 
    ){}

    public static function buscarPorNombre(string $nombre): ?usuario
    {
        $st = BD::conectar()->prepare('SELECT * FROM usuarios WHERE nombre = ?');
        $st->execute([$nombre]);
        $f = $st->fetch();
        return $f ? new Usuario((int)$f['id'], $f['nombre'], $f['password_hash']) : null;
    }

    public static function autenticar(string $nombre, string $password): ?usuario
    {
        $u = self::buscarPorNombre($nombre);
        if ($u && password_verify($password, $u->passwordHash)) {
            return $u;
        }
        return null;
    }

    public static function crear(string $nombre, string $password): void
    {
        $st = BD::conectar()->prepare(
            'INSERT INTO usuarios (nombre, password_hash) VALUES (?, ?)'
            );
            $st->execute([$nombre, password_hash($password, PASSWORD_DEFAULT)]);
    }
}