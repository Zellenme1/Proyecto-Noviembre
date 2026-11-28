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
    // Esta consulta base se comparte por las solicitudes enviadas y recibidas.
    private const SELECT_LISTA = 'SELECT st.id_solicitud, st.horas_estimadas, st.puntos_totales,
                                         st.fecha_propuesta, st.fecha_completado, st.estado,
                                         st.motivo_cancelacion,
                                         st.confirmado_cliente, st.confirmado_prestador,
                                         v.id_valoracion, v.puntuacion AS resena_puntuacion,
                                         v.comentario AS resena_comentario,
                                         s.nombre AS servicio_nombre,
                                         u.nombre AS otro_nombre, u.apellidos AS otro_apellidos
                                  FROM solicitudes_trueque st
                                  INNER JOIN ofertas_profesionales o ON o.id_oferta = st.id_oferta
                                  INNER JOIN servicios s ON s.id_servicio = o.id_servicio
                                  LEFT JOIN valoraciones v ON v.id_solicitud = st.id_solicitud';

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

    /** Puntos libres tras reservar las solicitudes que aún no se han completado. */
    public function getPuntosDisponibles(int $idCliente): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT GREATEST(0, u.puntos_saldo - COALESCE(SUM(st.puntos_totales), 0))
             FROM usuarios u
             LEFT JOIN solicitudes_trueque st
               ON st.id_cliente = u.id_usuario
              AND st.estado IN ('SOLICITADO', 'ACEPTADO', 'EN_EJECUCION')
             WHERE u.id_usuario = :id
             GROUP BY u.id_usuario, u.puntos_saldo"
        );
        $stmt->execute([':id' => $idCliente]);
        $puntos = $stmt->fetchColumn();
        return $puntos === false ? null : (int)$puntos;
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
     * @return array{id_solicitud:int, id_cliente:int, id_profesional:int, puntos_totales:int, estado:string}|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT st.id_solicitud, st.id_cliente, o.id_profesional, st.puntos_totales, st.estado
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
            'puntos_totales' => (int)$row['puntos_totales'],
            'estado' => (string)$row['estado'],
        ];
    }

    /**
     * Reserva los puntos de la nueva solicitud y devuelve su id.
     *
     * @return array{resultado:'creada'|'saldo_insuficiente'|'usuario_no_encontrado', id_solicitud?:int, puntos_disponibles:int}
     */
    public function create(int $idCliente, int $idOferta, float $horas, int $puntos, string $fechaPropuesta): array
    {
        // Todo el cálculo y el insert se hacen dentro de una transacción.
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT puntos_saldo FROM usuarios WHERE id_usuario = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $idCliente]);
            $saldo = $stmt->fetchColumn();
            if ($saldo === false) {
                $this->db->rollBack();
                return ['resultado' => 'usuario_no_encontrado', 'puntos_disponibles' => 0];
            }

            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(puntos_totales), 0)
                 FROM solicitudes_trueque
                 WHERE id_cliente = :id
                   AND estado IN ('SOLICITADO', 'ACEPTADO', 'EN_EJECUCION')"
            );
            $stmt->execute([':id' => $idCliente]);
            $puntosDisponibles = max(0, (int)$saldo - (int)$stmt->fetchColumn());
            if ($puntos > $puntosDisponibles) {
                $this->db->rollBack();
                return ['resultado' => 'saldo_insuficiente', 'puntos_disponibles' => $puntosDisponibles];
            }

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
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();

            return [
                'resultado' => 'creada',
                'id_solicitud' => $id,
                'puntos_disponibles' => $puntosDisponibles - $puntos,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Cambia el estado solo si sigue siendo el esperado (evita pisar un cambio
     * hecho a la vez por la otra parte). Devuelve true si se actualizó.
     */
    public function cambiarEstado(
        int $id,
        string $estadoActual,
        string $estadoNuevo,
        ?string $motivoCancelacion = null
    ): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE solicitudes_trueque
             SET estado = :nuevo, motivo_cancelacion = :motivo_cancelacion
             WHERE id_solicitud = :id AND estado = :actual'
        );
        $stmt->execute([
            ':nuevo' => $estadoNuevo,
            ':motivo_cancelacion' => $motivoCancelacion,
            ':id' => $id,
            ':actual' => $estadoActual,
        ]);
        return $stmt->rowCount() === 1;
    }

    /**
     * Completa un trabajo y transfiere sus puntos una sola vez.
     *
     * @return array{resultado:'completado'|'no_disponible'|'saldo_insuficiente', puntos_saldo_profesional?:int, puntos_transferidos?:int}
     */
    public function completar(int $id, int $idProfesional): array
    {
        // Comprueba primero ambos usuarios y después mueve los puntos.
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT st.id_cliente, o.id_profesional, st.puntos_totales, st.estado
                 FROM solicitudes_trueque st
                 INNER JOIN ofertas_profesionales o ON o.id_oferta = st.id_oferta
                 WHERE st.id_solicitud = :id
                 FOR UPDATE'
            );
            $stmt->execute([':id' => $id]);
            $solicitud = $stmt->fetch();

            if (!$solicitud
                || (int)$solicitud['id_profesional'] !== $idProfesional
                || (int)$solicitud['id_cliente'] === $idProfesional
                || !in_array($solicitud['estado'], ['ACEPTADO', 'EN_EJECUCION'], true)) {
                $this->db->rollBack();
                return ['resultado' => 'no_disponible'];
            }

            $idCliente = (int)$solicitud['id_cliente'];
            $puntos = (int)$solicitud['puntos_totales'];
            if ($puntos < 0) {
                $this->db->rollBack();
                return ['resultado' => 'no_disponible'];
            }

            $stmt = $this->db->prepare(
                'SELECT id_usuario, puntos_saldo
                 FROM usuarios
                 WHERE id_usuario IN (:id_cliente, :id_profesional)
                 ORDER BY id_usuario
                 FOR UPDATE'
            );
            $stmt->execute([':id_cliente' => $idCliente, ':id_profesional' => $idProfesional]);
            $saldos = [];
            foreach ($stmt->fetchAll() as $usuario) {
                $saldos[(int)$usuario['id_usuario']] = (int)$usuario['puntos_saldo'];
            }

            if (!isset($saldos[$idCliente], $saldos[$idProfesional]) || $saldos[$idCliente] < $puntos) {
                $this->db->rollBack();
                return ['resultado' => 'saldo_insuficiente'];
            }

            $stmt = $this->db->prepare(
                'UPDATE usuarios SET puntos_saldo = puntos_saldo - :puntos WHERE id_usuario = :id'
            );
            $stmt->execute([':puntos' => $puntos, ':id' => $idCliente]);

            $stmt = $this->db->prepare(
                'UPDATE usuarios SET puntos_saldo = puntos_saldo + :puntos WHERE id_usuario = :id'
            );
            $stmt->execute([':puntos' => $puntos, ':id' => $idProfesional]);

            $stmt = $this->db->prepare(
                'UPDATE solicitudes_trueque
                 SET estado = :nuevo, fecha_completado = CURRENT_TIMESTAMP
                 WHERE id_solicitud = :id AND estado = :actual'
            );
            $stmt->execute([
                ':nuevo' => 'COMPLETADO',
                ':id' => $id,
                ':actual' => $solicitud['estado'],
            ]);
            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return ['resultado' => 'no_disponible'];
            }

            $this->db->commit();
            return [
                'resultado' => 'completado',
                'puntos_saldo_profesional' => $saldos[$idProfesional] + $puntos,
                'puntos_transferidos' => $puntos,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
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
            'motivo_cancelacion' => $row['motivo_cancelacion'],
            'fecha_completado' => $row['fecha_completado'],
            'estado' => $row['estado'],
            'id_valoracion' => $row['id_valoracion'] === null ? null : (int)$row['id_valoracion'],
            'resena_puntuacion' => $row['resena_puntuacion'] === null ? null : (int)$row['resena_puntuacion'],
            'resena_comentario' => $row['resena_comentario'],
            'confirmado_cliente' => (bool)(int)$row['confirmado_cliente'],
            'confirmado_prestador' => (bool)(int)$row['confirmado_prestador'],
        ];
    }
}
