<?php
declare(strict_types=1);

namespace App\DAO;

use App\Models\Servicio;
use PDO;

/**
 * Acceso a datos del catálogo global de servicios.
 * Todas las lecturas hacen JOIN con categorias para devolver su nombre.
 */
class ServicioDAO
{
    private const SELECT = 'SELECT s.id_servicio, s.id_categoria, s.nombre, s.descripcion,
                                   s.puntos_hora_referencia, c.nombre AS categoria_nombre
                            FROM servicios s
                            INNER JOIN categorias c ON c.id_categoria = s.id_categoria';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /** @return Servicio[] */
    public function getAll(): array
    {
        $stmt = $this->db->query(self::SELECT . ' ORDER BY c.nombre, s.nombre');
        return array_map(fn(array $row) => $this->mapToObject($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Servicio
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE s.id_servicio = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToObject($row) : null;
    }

    /** Devuelve el id del servicio creado. */
    public function create(Servicio $servicio): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO servicios (id_categoria, nombre, descripcion, puntos_hora_referencia)
             VALUES (:id_categoria, :nombre, :descripcion, :puntos)'
        );
        $stmt->execute([
            ':id_categoria' => $servicio->getIdCategoria(),
            ':nombre' => $servicio->getNombre(),
            ':descripcion' => $servicio->getDescripcion(),
            ':puntos' => $servicio->getPuntosHoraReferencia(),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(Servicio $servicio): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE servicios
             SET id_categoria = :id_categoria, nombre = :nombre,
                 descripcion = :descripcion, puntos_hora_referencia = :puntos
             WHERE id_servicio = :id'
        );
        return $stmt->execute([
            ':id' => $servicio->getId(),
            ':id_categoria' => $servicio->getIdCategoria(),
            ':nombre' => $servicio->getNombre(),
            ':descripcion' => $servicio->getDescripcion(),
            ':puntos' => $servicio->getPuntosHoraReferencia(),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM servicios WHERE id_servicio = :id');
        return $stmt->execute([':id' => $id]);
    }

    /** Nº de ofertas de profesionales que usan este servicio. */
    public function countOfertas(int $idServicio): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ofertas_profesionales WHERE id_servicio = :id'
        );
        $stmt->execute([':id' => $idServicio]);
        return (int)$stmt->fetchColumn();
    }

    private function mapToObject(array $row): Servicio
    {
        return new Servicio(
            (int)$row['id_servicio'],
            (int)$row['id_categoria'],
            $row['nombre'],
            $row['descripcion'],
            (int)$row['puntos_hora_referencia'],
            $row['categoria_nombre'] ?? null
        );
    }
}
