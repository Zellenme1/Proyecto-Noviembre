<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * Base común de los controladores de la API JSON.
 */
abstract class ApiController
{
    // Convierte los datos a JSON y fija el código HTTP de respuesta.
    protected function jsonResponse(mixed $data, int $statusCode = 200): never
    {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Lee el cuerpo de la petición como JSON; responde 400 si no es válido. */
    protected function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw === false ? '' : $raw, true);

        if (!is_array($data)) {
            $this->jsonResponse(['error' => 'El cuerpo de la petición debe ser un JSON válido'], 400);
        }
        return $data;
    }
}
