<?php
declare(strict_types=1);

namespace App\DAO;

use PDO;

class  ValoracionDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /** Media y número de reseñas de un profesional (todas sus ofertas juntas). */
    public function resumenDeProfesional(int $idProfesional): array
    {
        $stmt = $this->db->prepare(
            'SELECT AVG(v.puntuacion) AS media, COUNT(*) AS total
             FROM valoraciones v
             INNER JOIN solicitudes_trueque s ON s.id_solicitud = v.id_solicitud
             INNER JOIN ofertas_profesionales o ON o.id_oferta = s.id_oferta
             WHERE o.id_profesional = :id'
        );
        $stmt->execute([':id' => $idProfesional]);
        $fila = $stmt->fetch();

        return [
            // Sin reseñas, AVG devuelve NULL: se deja como null y no como 0
            'media' => $fila['media'] === null ? null : round((float)$fila['media'], 1),
            'total' => (int)$fila['total'],
        ];
    }

    public function deProfesional(int $idProfesional):array
    {
        $stmt = $this->db->prepare(
            'SELECT v.puntuacion, v.comentario, v.fecha_valoracion
                    sv.nombres AS servicio,
                    c.nombre AS cliente_nombre, c.apellidos, AS cliente_apellidos
            FROM valoraciones v
             INNER JOIN solicitudes_trueque s ON s.id_solicitud = v.id_solicitud
             INNER JOIN ofertas_profesionales o ON o.id_oferta = s.id_oferta
             INNER JOIN servicios sv ON sv.id_servicio = o.id_servicio
             INNER JOIN usuarios c ON c.id_usuario = s.id_cliente
             WHERE o.id_profesional = :id
             ORDER BY v.fecha_valoracion DESC, v.id_valoracion DESC'
        );
        $stmt->execute([':id' => $idProfesional]);

        return array_map(fn(array $fila) => [
            'puntuacion' => (int)$fila['puntuacion'],
            'comentario' => $fila['comentario'],
            'fecha' => $fila['fecha_valoracion'],
            'servicio' => $fila['servicio'],
            // Privacidad: solo el nombre y la inicial del apellido ("Marta S.")
            'autor' => $fila['cliente_nombre'] . ' ' . mb_substr($fila['cliente_apellidos'], 0, 1) . '.',
        ], $stmt->fetchAll());
    }
}