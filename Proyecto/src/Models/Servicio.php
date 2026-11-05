<?php
namespace  App\Models;

class Servicio implements \JsonSerializable {
    private ?int $id;
    private string $nombre;
    private string $descripcion;
    private int $idUsuario;
    private int $idCategoria;
    private string $fechaCreacion;

    public function __construct(
        ?int $id,
        string $nombre,
        string $descripcion,
        int $idUsuario,
        int $idCategoria,
        string $fechaCreacion
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->idUsuario = $idUsuario;
        $this->idCategoria = $idCategoria;
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getId(): ?int {return $this->id;}
    public function getNombre(): string {return $this->nombre;}
    public function getDescripcion(): string {return $this->descripcion;}
    public function getIdUsuario(): int {return $this->idUsuario;}
    public function getIdCategoria(): int {return $this->idCategoria;}
    public function getFechaCreacion(): string {return $this->fechaCreacion;}

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'idUsuario' => $this->idUsuario,
            'idCategoria' => $this->idCategoria,
            'fechaCreacion' => $this->fechaCreacion
        ];
    }
}