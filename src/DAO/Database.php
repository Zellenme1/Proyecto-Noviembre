<?php
declare(strict_types=1);

namespace App\DAO;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $host = getenv('DB_HOST') ?: 'db';
        $name = getenv('DB_NAME') ?: 'trueques_db';
        $user = getenv('DB_USER') ?: 'usuario_trueque';
        $pass = getenv('DB_PASS') ?: 'password_secret';

        try {
            $this->connection = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            $comprobacion = $this->connection->prepare(
                'SELECT COUNT(*)
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :tabla
                   AND COLUMN_NAME = :columna'
            );
            $comprobacion->execute([
                ':tabla' => 'usuarios',
                ':columna' => 'curriculum',
            ]);

            if ((int)$comprobacion->fetchColumn() === 0) {
                $this->connection->exec(
                    'ALTER TABLE usuarios
                     ADD COLUMN curriculum VARCHAR(255) NULL'
                );
            }

            $comprobacion->execute([
                ':tabla' => 'solicitudes_trueque',
                ':columna' => 'motivo_cancelacion',
            ]);
            if ((int)$comprobacion->fetchColumn() === 0) {
                $this->connection->exec(
                    'ALTER TABLE solicitudes_trueque
                     ADD COLUMN motivo_cancelacion TEXT NULL'
                );
            }
        } catch (PDOException $e) {
            // El detalle (host, usuario...) va al log del servidor, nunca al cliente.
            error_log('Error de conexión a la BD: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Error interno del servidor'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception('No se permite deserializar una instancia de Singleton.');
    }
}
