<?php
require_once 'bd.php';

class Producto
{
    public function __construct(
        public ?int $id = null,
        public string $nombre = '',
        public float $precio = 0
    ) {}

    // Convierte una fila de la BD en un objeto Producto
    private static function desdeFila(array $f): Producto
    {
        return new Producto((int)$f['id'], $f['nombre'], (float)$f['precio']);
    }

    public static function todos(): array
    {
        $filas = BD::conectar()->query('SELECT * FROM productos ORDER BY id')->fetchAll();
        return array_map([self::class, 'desdeFila'], $filas);
    }

    public static function buscar(int $id): ?Producto
    {
        $st = BD::conectar()->prepare('SELECT * FROM productos WHERE id = ?');
        $st->execute([$id]);
        $fila = $st->fetch();
        return $fila ? self::desdeFila($fila) : null;
    }

    public function guardar(): void
    {
        $bd = BD::conectar();
        if ($this->id === null) {
            $st = $bd->prepare('INSERT INTO productos (nombre, precio) VALUES (?, ?)');
            $st->execute([$this->nombre, $this->precio]);
            $this->id = (int)$bd->lastInsertId();
        } else {
            $st = $bd->prepare('UPDATE productos SET nombre = ?, precio = ? WHERE id = ?');
            $st->execute([$this->nombre, $this->precio, $this->id]);
        }
    }

    public function borrar(): void
    {
        $st = BD::conectar()->prepare('DELETE FROM productos WHERE id = ?');
        $st->execute([$this->id]);
    }
}