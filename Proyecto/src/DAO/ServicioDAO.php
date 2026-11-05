<?php
namespace App\DAO;

use App\Models\Servicio;
use PDO;

class ServicioDAO {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM servicios ORDER BY fecha_creacion DESC");
        return array_map(fn($row) => $this->mapToObject($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Servicio {
        $stmt = $this->db->prepare("SELECT * FROM servicios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToObject($row) : null;
    }

    public function getByCategoria(int $idCategoria): array {
        $stmt = $this->db->prepare("SELECT * FROM servicios WHERE id_categoria = :id_categoria");
        $stmt->execute(['id_categoria' => $idCategoria]);
        return array_map(fn($row) => $this->mapToObject($row), $stmt->fetchAll());
    }

    public function create(Servicio $servicio): bool {
        $sql = "INSERT INTO servicios (id, titulo, descripcion, id_categoria) 
        VALUES (:id, :titulo, :descripcion, :id_categoria)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $servicio->getId(),
            'titulo' => $servicio->getTitulo(),
            'descripcion' => $servicio->getDescripcion(),
            'id_categoria' => $servicio->getIdCategoria()
        ]);
    }

    public function update(Servicio $servicio): bool {
        $sql = "UPDATE servicios
        SET titulo = :titulo, descripcion = :descripcion, id_categoria = :id_categoria
        WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $servicio->getId(),
            'titulo' => $servicio->getTitulo(),
            'descripcion' => $servicio->getDescripcion(),
            'id_categoria' => $servicio->getIdCategoria()
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM servicios WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }   

    private function mapToObject(array $row): Servicio {
        return new Servicio(
            (int)$row['id'],
            $row['titulo'],
            $row['descripcion'],
            (int)$row['id_usuario'],
            (int)$row['id_categoria'],
            $row['fecha_creacion'] ?? null
        );
    }
}