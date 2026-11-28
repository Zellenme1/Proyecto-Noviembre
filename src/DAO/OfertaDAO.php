<?php
declare(strict_types=1);

namespace App\DAO;

use PDO;

/**
 * Lectura de las ofertas de los profesionales (tabla `ofertas_profesionales`)
 * junto con el servicio, la categoría y el nombre del profesional.
 * Solo devuelve ofertas activas de usuarios activos. Nunca expone email,
 * teléfono ni hash del profesional: es información pública del buscador.
 */
class OfertaDAO
{
    private const SELECT = 'SELECT o.id_oferta, o.id_profesional, o.id_servicio, o.puntos_hora,
                                   o.descripcion_personal, s.nombre AS servicio_nombre,
                                   s.id_categoria, c.nombre AS categoria_nombre,
                                   u.nombre AS profesional_nombre, u.apellidos AS profesional_apellidos,
                                   u.foto AS profesional_foto
                            FROM ofertas_profesionales o
                            INNER JOIN servicios s ON s.id_servicio = o.id_servicio
                            INNER JOIN categorias c ON c.id_categoria = s.id_categoria
                            INNER JOIN usuarios u ON u.id_usuario = o.id_profesional
                            WHERE o.activo = 1 AND u.activo = 1';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscar(?int $idCategoria, string $texto): array
    {
        // Añade los filtros solo si el usuario los escribió.
        $sql = self::SELECT;
        $params = [];

        if ($idCategoria !== null) {
            $sql .= ' AND s.id_categoria = :id_categoria';
            $params[':id_categoria'] = $idCategoria;
        }

        if ($texto !== '') {
            // Se escapan % y _ para que el texto del usuario se busque literalmente.
            $like = '%' . addcslashes($texto, '%_\\') . '%';
            $sql .= ' AND (s.nombre LIKE :q1 OR c.nombre LIKE :q2
                           OR o.descripcion_personal LIKE :q3 OR u.nombre LIKE :q4)';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        $sql .= ' ORDER BY c.nombre, s.nombre, o.puntos_hora';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->mapToArray($row), $stmt->fetchAll());
    }

    /** @return array<string, mixed>|null */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . ' AND o.id_oferta = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToArray($row) : null;
    }

    /**
     * Da de alta varias ofertas de golpe para un profesional, con la tarifa de
     * referencia de cada servicio. Los ids que no existan simplemente no se crean:
     * quien llama compara el número devuelto con los que pidió.
     *
     * @param int[] $idsServicio
     * @return int cuántas ofertas se han creado
     */
    public function crearVarias(int $idProfesional, array $idsServicio): int
    {
        if ($idsServicio === []) {
            return 0;
        }

        $marcadores = implode(',', array_fill(0, count($idsServicio), '?'));
        $stmt = $this->db->prepare(
            'INSERT INTO ofertas_profesionales (id_profesional, id_servicio, puntos_hora)
             SELECT ' . $idProfesional . ', id_servicio, puntos_hora_referencia
             FROM servicios WHERE id_servicio IN (' . $marcadores . ')'
        );
        $stmt->execute(array_values($idsServicio));
        return $stmt->rowCount();
    }

    /** @return array<string, mixed> */
    private function mapToArray(array $row): array
    {
        return [
            'id_oferta' => (int)$row['id_oferta'],
            'id_profesional' => (int)$row['id_profesional'],
            'id_servicio' => (int)$row['id_servicio'],
            'servicio_nombre' => $row['servicio_nombre'],
            'id_categoria' => (int)$row['id_categoria'],
            'categoria_nombre' => $row['categoria_nombre'],
            'profesional' => trim($row['profesional_nombre'] . ' ' . $row['profesional_apellidos']),
            'puntos_hora' => (int)$row['puntos_hora'],
            'descripcion_personal' => $row['descripcion_personal'],
            'foto' => $row['profesional_foto'],
        ];
    }
}
