<?php
declare(strict_types=1);

namespace App\DAO;

use App\Models\Categoria;
use PDO;

class CategoriaDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /** @return Categoria[] */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id_categoria, nombre, descripcion FROM categorias ORDER BY nombre'
        );
        return array_map(fn(array $row) => $this->mapToObject($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Categoria
    {
        $stmt = $this->db->prepare(
            'SELECT id_categoria, nombre, descripcion FROM categorias WHERE id_categoria = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToObject($row) : null;
    }

    private function mapToObject(array $row): Categoria
    {
        return new Categoria((int)$row['id_categoria'], $row['nombre'], $row['descripcion']);
    }
}
