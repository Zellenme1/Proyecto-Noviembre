<?php
declare(strict_types=1);

namespace App\Models;

class Usuario implements \JsonSerializable
{
    public const ROL_ADMIN = 'ADMIN';
    public const ROL_PROFESIONAL = 'PROFESIONAL';
    public const PUNTOS_BIENVENIDA = 50;

    public function __construct(
        private ?int $id,
        private string $nombre,
        private string $apellidos,
        private string $email,
        private string $passwordHash,
        private ?string $telefono,
        private string $rol,
        private int $puntosSaldo,
        private ?string $fechaRegistro = null,
        private bool $activo = true,
        private ?string $foto = null
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellidos(): string { return $this->apellidos; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getTelefono(): ?string { return $this->telefono; }
    public function getRol(): string { return $this->rol; }
    public function getPuntosSaldo(): int { return $this->puntosSaldo; }
    public function getFechaRegistro(): ?string { return $this->fechaRegistro; }
    public function isActivo(): bool { return $this->activo; }
    public function getFoto(): ?string { return $this->foto; }

    /** Nunca incluye el hash de la contraseña. */
    public function jsonSerialize(): array
    {
        return [
            'id_usuario' => $this->id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'rol' => $this->rol,
            'puntos_saldo' => $this->puntosSaldo,
            'fecha_registro' => $this->fechaRegistro,
            'foto' => $this->foto,
        ];
    }
}
