<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Categoría del catálogo global (tabla `categorias`).
 */
class Categoria implements \JsonSerializable
{
    public function __construct(
        private ?int $id,
        private string $nombre,
        private ?string $descripcion = null
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }

    public function jsonSerialize(): array
    {
        return [
            'id_categoria' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
        ];
    }
}
