<?php
declare(strict_types=1);

namespace App\DAO;

use App\Models\Usuario;
use PDO;

class UsuarioDAO
{
    private const COLUMNAS = 'id_usuario, nombre, apellidos, email, password, telefono,
                              rol, puntos_saldo, fecha_registro, activo, foto';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    public function getById(int $id): ?Usuario
    {
        $stmt = $this->db->prepare('SELECT ' . self::COLUMNAS . ' FROM usuarios WHERE id_usuario = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToObject($row) : null;
    }

    public function getByEmail(string $email): ?Usuario
    {
        $stmt = $this->db->prepare('SELECT ' . self::COLUMNAS . ' FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? $this->mapToObject($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    /** Devuelve el id del usuario creado. */
    public function create(Usuario $usuario): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, apellidos, email, password, telefono, rol, puntos_saldo)
             VALUES (:nombre, :apellidos, :email, :password, :telefono, :rol, :puntos)'
        );
        $stmt->execute([
            ':nombre' => $usuario->getNombre(),
            ':apellidos' => $usuario->getApellidos(),
            ':email' => $usuario->getEmail(),
            ':password' => $usuario->getPasswordHash(),
            ':telefono' => $usuario->getTelefono(),
            ':rol' => $usuario->getRol(),
            ':puntos' => $usuario->getPuntosSaldo(),
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Guarda la ruta pública de la foto de perfil (o NULL para quitarla). */
    public function actualizarFoto(int $idUsuario, ?string $ruta): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET foto = :foto WHERE id_usuario = :id');
        $stmt->execute([':foto' => $ruta, ':id' => $idUsuario]);
    }

    private function mapToObject(array $row): Usuario
    {
        return new Usuario(
            (int)$row['id_usuario'],
            $row['nombre'],
            $row['apellidos'],
            $row['email'],
            $row['password'],
            $row['telefono'],
            $row['rol'],
            (int)$row['puntos_saldo'],
            $row['fecha_registro'],
            (bool)(int)$row['activo'],
            $row['foto']
        );
    }
}
