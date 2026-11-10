<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Servicio del catálogo global (tabla `servicios`), definido solo por el admin.
 * Los profesionales NO crean servicios: crean ofertas (ofertas_profesionales)
 * sobre un servicio del catálogo.
 */
class Servicio implements \JsonSerializable
{
    public function __construct(
        private ?int $id,
        private int $idCategoria,
        private string $nombre,
        private ?string $descripcion,
        private int $puntosHoraReferencia,
        private ?string $categoriaNombre = null // solo al leer con JOIN
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getIdCategoria(): int { return $this->idCategoria; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getPuntosHoraReferencia(): int { return $this->puntosHoraReferencia; }
    public function getCategoriaNombre(): ?string { return $this->categoriaNombre; }

    public function jsonSerialize(): array
    {
        return [
            'id_servicio' => $this->id,
            'id_categoria' => $this->idCategoria,
            'categoria_nombre' => $this->categoriaNombre,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'puntos_hora_referencia' => $this->puntosHoraReferencia,
        ];
    }
}
