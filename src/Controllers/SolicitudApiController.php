<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\DAO\OfertaDAO;
use App\DAO\SolicitudDAO;
use App\Models\Usuario;

/**
 * Endpoints (solo usuarios PROFESIONAL con sesión):
 *   GET  /api/solicitudes        -> {recibidas: [...], enviadas: [...]}
 *   GET  /api/puntos-disponibles -> saldo menos puntos de solicitudes activas
 *   POST /api/solicitudes        -> crea una solicitud sobre una oferta
 *   PUT  /api/solicitudes/{id}   -> {accion: aceptar | rechazar | cancelar, motivo_cancelacion?: string}
 */
class SolicitudApiController extends ApiController
{
    private SolicitudDAO $solicitudDAO;
    private OfertaDAO $ofertaDAO;

    public function __construct()
    {
        $this->solicitudDAO = new SolicitudDAO();
        $this->ofertaDAO = new OfertaDAO();
    }

    public function index(): void
    {
        // Separa las solicitudes enviadas de las que llegan al usuario.
        $usuario = Auth::requireRole(Usuario::ROL_PROFESIONAL);
        $id = (int)$usuario->getId();

        $this->jsonResponse([
            'recibidas' => $this->solicitudDAO->getRecibidas($id),
            'enviadas' => $this->solicitudDAO->getEnviadas($id),
        ]);
    }

    public function puntosDisponibles(): void
    {
        // Devuelve el saldo que queda después de reservar solicitudes.
        $usuario = Auth::requireRole(Usuario::ROL_PROFESIONAL);
        $puntos = $this->solicitudDAO->getPuntosDisponibles((int)$usuario->getId());
        if ($puntos === null) {
            $this->jsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        $this->jsonResponse(['puntos_disponibles' => $puntos]);
    }

    public function store(): void
    {
        // Valida la oferta y crea la solicitud si hay puntos suficientes.
        $usuario = Auth::requireRole(Usuario::ROL_PROFESIONAL);
        $in = $this->readJsonBody();

        $idOferta = filter_var($in['id_oferta'] ?? null, FILTER_VALIDATE_INT);
        $oferta = $idOferta === false ? null : $this->ofertaDAO->getById($idOferta);
        if ($oferta === null) {
            $this->jsonResponse(['error' => 'La oferta no existe o ya no está disponible'], 404);
        }
        if ($oferta['id_profesional'] === $usuario->getId()) {
            $this->jsonResponse(['error' => 'No puedes solicitar tu propio servicio'], 422);
        }

        $errores = [];

        // Horas: entre 0,5 y 24, de media en media hora.
        $horas = filter_var($in['horas_estimadas'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($horas === false || $horas < 0.5 || $horas > 24 || fmod($horas * 2, 1.0) !== 0.0) {
            $errores['horas_estimadas'] = 'Indica entre 0,5 y 24 horas, de media en media hora';
        }

        // Fecha propuesta: formato de <input type="datetime-local"> y en el futuro.
        $fechaTexto = is_string($in['fecha_propuesta'] ?? null) ? trim($in['fecha_propuesta']) : '';
        $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $fechaTexto);
        if ($fecha === false
            || $fecha->format('Y-m-d\TH:i') !== $fechaTexto
            || $fecha <= new \DateTimeImmutable('now')) {
            $errores['fecha_propuesta'] = 'Indica una fecha y hora futuras';
        }

        // Coste: puntos/hora de la oferta x horas, redondeado hacia arriba.
        $puntos = 0;
        if (!$errores) {
            $puntos = (int)ceil(round($oferta['puntos_hora'] * $horas, 2));
        }

        if ($errores) {
            $this->jsonResponse(['error' => 'Datos no válidos', 'errores' => $errores], 422);
        }

        $resultado = $this->solicitudDAO->create(
            (int)$usuario->getId(),
            $oferta['id_oferta'],
            $horas,
            $puntos,
            $fecha->format('Y-m-d H:i:s')
        );
        if ($resultado['resultado'] === 'saldo_insuficiente') {
            $disponibles = $resultado['puntos_disponibles'];
            $this->jsonResponse([
                'error' => 'Datos no válidos',
                'errores' => [
                    'horas_estimadas' => "Necesitas {$puntos} puntos y tienes {$disponibles} disponibles después de reservar tus solicitudes sin completar",
                ],
            ], 422);
        }
        if ($resultado['resultado'] !== 'creada') {
            $this->jsonResponse(['error' => 'No se pudo crear la solicitud'], 404);
        }

        $this->jsonResponse([
            'message' => 'Solicitud enviada',
            'id_solicitud' => $resultado['id_solicitud'],
            'puntos_totales' => $puntos,
            'puntos_disponibles' => $resultado['puntos_disponibles'],
        ], 201);
    }

    public function update(int $id): void
    {
        // Ejecuta aceptar, rechazar, cancelar o completar.
        $usuario = Auth::requireRole(Usuario::ROL_PROFESIONAL);
        $in = $this->readJsonBody();
        $accion = is_string($in['accion'] ?? null) ? $in['accion'] : '';

        $solicitud = $this->solicitudDAO->getById($id);
        $yo = $usuario->getId();
        $esCliente = $solicitud !== null && $solicitud['id_cliente'] === $yo;
        $esPrestador = $solicitud !== null && $solicitud['id_profesional'] === $yo;

        // Si no es parte de la solicitud se responde 404 para no revelar que existe.
        if ($solicitud === null || (!$esCliente && !$esPrestador)) {
            $this->jsonResponse(['error' => 'Solicitud no encontrada'], 404);
        }

        if ($accion === 'completar') {
            if (!$esPrestador) {
                $this->jsonResponse(['error' => 'Solo el profesional puede marcar el servicio como hecho'], 403);
            }
            if (!in_array($solicitud['estado'], ['ACEPTADO', 'EN_EJECUCION'], true)) {
                $this->jsonResponse(['error' => 'La solicitud no está en curso'], 409);
            }

            $resultado = $this->solicitudDAO->completar($id, (int)$yo);
            if ($resultado['resultado'] === 'saldo_insuficiente') {
                $this->jsonResponse(['error' => 'El cliente ya no tiene puntos suficientes para completar el servicio'], 409);
            }
            if ($resultado['resultado'] !== 'completado') {
                $this->jsonResponse(['error' => 'La solicitud ya no está disponible para completar'], 409);
            }

            $this->jsonResponse([
                'message' => 'Servicio completado y puntos transferidos',
                'estado' => 'COMPLETADO',
                'puntos_transferidos' => $resultado['puntos_transferidos'],
                'puntos_saldo' => $resultado['puntos_saldo_profesional'],
            ]);
        }

        // acción => [¿puede hacerla este usuario?, estados desde los que vale, estado nuevo]
        $reglas = [
            'aceptar' => [$esPrestador, ['SOLICITADO'], 'ACEPTADO'],
            'rechazar' => [$esPrestador, ['SOLICITADO'], 'RECHAZADO'],
            'cancelar' => [$esCliente, ['SOLICITADO', 'ACEPTADO'], 'CANCELADO'],
        ];

        if (!isset($reglas[$accion])) {
            $this->jsonResponse(['error' => 'Acción no válida'], 422);
        }

        [$permitido, $estadosValidos, $estadoNuevo] = $reglas[$accion];

        if (!$permitido) {
            $this->jsonResponse(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }
        if (!in_array($solicitud['estado'], $estadosValidos, true)) {
            $this->jsonResponse(['error' => 'La solicitud ya no está en un estado que permita esta acción'], 409);
        }
        $motivoCancelacion = null;
        if ($accion === 'cancelar') {
            $motivoCancelacion = is_string($in['motivo_cancelacion'] ?? null)
                ? trim($in['motivo_cancelacion'])
                : '';
            if ($motivoCancelacion === '' || mb_strlen($motivoCancelacion) > 1000) {
                $this->jsonResponse([
                    'error' => 'Indica el motivo de la cancelación (máximo 1000 caracteres)',
                    'errores' => ['motivo_cancelacion' => 'El motivo es obligatorio y no puede superar los 1000 caracteres'],
                ], 422);
            }
        }

        if (!$this->solicitudDAO->cambiarEstado(
            $id,
            $solicitud['estado'],
            $estadoNuevo,
            $motivoCancelacion
        )) {
            $this->jsonResponse(['error' => 'La solicitud ha cambiado mientras la mirabas. Recarga la página'], 409);
        }

        $this->jsonResponse(['message' => 'Solicitud actualizada', 'estado' => $estadoNuevo]);
    }
}
