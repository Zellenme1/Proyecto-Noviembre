<?php
declare(strict_types=1);

namespace App\DAO;

use PDO;

/**
 * Acceso a datos de las solicitudes de trueque (tabla `solicitudes_trueque`).
 *
 * Cada solicitud tiene dos partes: el CLIENTE (quien la envía y paga los puntos)
 * y el PRESTADOR (el profesional dueño de la oferta). Por eso hay dos listados:
 * enviadas (soy cliente) y recibidas (soy prestador).
 */
class SolicitudDAO
{
    private const SELECT_LISTA = 'SELECT st.id_solicitud, st.horas_estimadas, st.puntos_totales,
                                         st.fecha_propuesta, st.estado,
                                         st.confirmado_cliente, st.confirmado_prestador,
                                         s.nombre AS servicio_nombre,
                                         u.nombre AS otro_nombre, u.apellidos AS otro_apellidos
                                  FROM solicitudes_trueque st
                                  INNER JOIN ofertas_profesionales o ON o.id_oferta = st.id_oferta
                                  INNER JOIN servicios s ON s.id_servicio = o.id_servicio';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Solicitudes que yo he enviado. "otra_persona" es el profesional.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEnviadas(int $idCliente): array
    {
        $stmt = $this->db->prepare(
            self::SELECT_LISTA . '
             INNER JOIN usuarios u ON u.id_usuario = o.id_profesional
             WHERE st.id_cliente = :id
             ORDER BY st.fecha_propuesta DESC, st.id_solicitud DESC'
        );
        $stmt->execute([':id' => $idCliente]);
        return array_map(fn(array $row) => $this->mapToArray($row), $stmt->fetchAll());
    }

    /**
     * Solicitudes que me han enviado a mí. "otra_persona" es el cliente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecibidas(int $idProfesional): array
    {
        $stmt = $this->db->prepare(
            self::SELECT_LISTA . '
             INNER JOIN usuarios u ON u.id_usuario = st.id_cliente
             WHERE o.id_profesional = :id
             ORDER BY st.fecha_propuesta DESC, st.id_solicitud DESC'
        );
        $stmt->execute([':id' => $idProfesional]);
        return array_map(fn(array $row) => $this->mapToArray($row), $stmt->fetchAll());
    }

    /**
     * Datos mínimos para decidir quién puede hacer qué con una solicitud.
     *
     * @return array{id_solicitud:int, id_cliente:int, id_profesional:int, estado:string}|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT st.id_solicitud, st.id_cliente, o.id_profesional, st.estado
             FROM solicitudes_trueque st
             INNER JOIN ofertas_profesionales o ON o.id_oferta = st.id_oferta
             WHERE st.id_solicitud = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return [
            'id_solicitud' => (int)$row['id_solicitud'],
            'id_cliente' => (int)$row['id_cliente'],
            'id_profesional' => (int)$row['id_profesional'],
            'estado' => (string)$row['estado'],
        ];
    }

    /** Devuelve el id de la solicitud creada (estado inicial: SOLICITADO). */
    public function create(int $idCliente, int $idOferta, float $horas, int $puntos, string $fechaPropuesta): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO solicitudes_trueque (id_cliente, id_oferta, horas_estimadas, puntos_totales, fecha_propuesta)
             VALUES (:id_cliente, :id_oferta, :horas, :puntos, :fecha)'
        );
        $stmt->execute([
            ':id_cliente' => $idCliente,
            ':id_oferta' => $idOferta,
            ':horas' => number_format($horas, 2, '.', ''),
            ':puntos' => $puntos,
            ':fecha' => $fechaPropuesta,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Cambia el estado solo si sigue siendo el esperado (evita pisar un cambio
     * hecho a la vez por la otra parte). Devuelve true si se actualizó.
     */
    public function cambiarEstado(int $id, string $estadoActual, string $estadoNuevo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE solicitudes_trueque SET estado = :nuevo
             WHERE id_solicitud = :id AND estado = :actual'
        );
        $stmt->execute([':nuevo' => $estadoNuevo, ':id' => $id, ':actual' => $estadoActual]);
        return $stmt->rowCount() === 1;
    }

    /** @return array<string, mixed> */
    private function mapToArray(array $row): array
    {
        return [
            'id_solicitud' => (int)$row['id_solicitud'],
            'servicio_nombre' => $row['servicio_nombre'],
            'otra_persona' => trim($row['otro_nombre'] . ' ' . $row['otro_apellidos']),
            'horas_estimadas' => (float)$row['horas_estimadas'],
            'puntos_totales' => (int)$row['puntos_totales'],
            'fecha_propuesta' => $row['fecha_propuesta'],
            'estado' => $row['estado'],
            'confirmado_cliente' => (bool)(int)$row['confirmado_cliente'],
            'confirmado_prestador' => (bool)(int)$row['confirmado_prestador'],
        ];
    }
}
