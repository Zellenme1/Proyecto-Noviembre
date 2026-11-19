<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\DAO\UsuarioDAO;
use App\Models\Usuario;

/**
 * Endpoints: POST /api/login, POST /api/logout, POST /api/registro
 *
 * El registro admite una lista opcional de servicios ("servicios": [ids]) que el
 * nuevo profesional ofrece desde el primer día, con la tarifa de referencia.
 */
class AuthApiController extends ApiController
{
    private const MAX_SERVICIOS_REGISTRO = 20;

    // Hash de relleno: se verifica siempre una contraseña aunque el email no exista,
    // para que el tiempo de respuesta no revele qué emails están registrados.
    private const HASH_RELLENO = '$2y$10$jffo5kO6QP4AdaemjQwnv.VoHCC4aaraUQbg4R9QEEUMOO5H9VMS2';

    private UsuarioDAO $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function login(): void
    {
        $in = $this->readJsonBody();
        $email = is_string($in['email'] ?? null) ? strtolower(trim($in['email'])) : '';
        $password = is_string($in['password'] ?? null) ? $in['password'] : '';

        if ($email === '' || $password === '') {
            $this->jsonResponse(['error' => 'Introduce tu email y tu contraseña'], 422);
        }

        $usuario = $this->usuarioDAO->getByEmail($email);
        $hashValido = password_verify($password, $usuario?->getPasswordHash() ?? self::HASH_RELLENO);

        // Mensaje único para email inexistente, contraseña errónea o cuenta de baja.
        if ($usuario === null || !$hashValido || !$usuario->isActivo()) {
            $this->jsonResponse(['error' => 'Email o contraseña incorrectos'], 401);
        }

        Auth::login($usuario);

        $this->jsonResponse([
            'usuario' => $usuario,
            'csrf_token' => Auth::csrfToken(),
            'redirect' => $usuario->getRol() === Usuario::ROL_ADMIN ? '/servicios-spa' : '/',
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->jsonResponse(['message' => 'Sesión cerrada']);
    }

    public function registro(): void
    {
        ['errores' => $errores, 'datos' => $d] = $this->validarRegistro($this->readJsonBody());

        if (!isset($errores['email']) && $this->usuarioDAO->emailExists($d['email'])) {
            $this->jsonResponse(['error' => 'Ya existe una cuenta con ese email', 'errores' => ['email' => 'Email ya registrado']], 409);
        }
        if ($errores) {
            $this->jsonResponse(['error' => 'Datos no válidos', 'errores' => $errores], 422);
        }

        // El rol y los puntos de bienvenida los fija SIEMPRE el servidor:
        // nada de lo que envíe el cliente puede crear un ADMIN ni cambiar el saldo.
        $nuevo = new Usuario(
            null,
            $d['nombre'],
            $d['apellidos'],
            $d['email'],
            password_hash($d['password'], PASSWORD_DEFAULT),
            $d['telefono'],
            Usuario::ROL_PROFESIONAL,
            Usuario::PUNTOS_BIENVENIDA
        );

        try {
            $id = $this->usuarioDAO->registrar($nuevo, $d['servicios']);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'email')) {
                // email duplicado por carrera entre dos registros
                $this->jsonResponse(['error' => 'Ya existe una cuenta con ese email'], 409);
            }
            throw $e;
        }

        if ($id === null) { // alguno de los servicios ya no existe
            $this->jsonResponse([
                'error' => 'Datos no válidos',
                'errores' => ['servicios' => 'Alguno de los servicios elegidos ya no existe. Recarga la página'],
            ], 422);
        }

        $this->jsonResponse([
            'usuario' => $this->usuarioDAO->getById($id),
            'servicios_creados' => count($d['servicios']),
        ], 201);
    }

    /**
     * Valida el JSON del registro.
     *
     * @return array{errores: array<string,string>, datos: array}
     */
    private function validarRegistro(array $in): array
    {
        $errores = [];

        $nombre = is_string($in['nombre'] ?? null) ? trim($in['nombre']) : '';
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $errores['nombre'] = 'El nombre es obligatorio (máximo 100 caracteres)';
        }

        $apellidos = is_string($in['apellidos'] ?? null) ? trim($in['apellidos']) : '';
        if ($apellidos === '' || mb_strlen($apellidos) > 100) {
            $errores['apellidos'] = 'Los apellidos son obligatorios (máximo 100 caracteres)';
        }

        $email = is_string($in['email'] ?? null) ? strtolower(trim($in['email'])) : '';
        if (mb_strlen($email) > 150 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errores['email'] = 'Introduce un email válido';
        }

        $password = is_string($in['password'] ?? null) ? $in['password'] : '';
        if (mb_strlen($password) < 8 || strlen($password) > 72) { // 72 bytes: límite de bcrypt
            $errores['password'] = 'La contraseña debe tener entre 8 y 72 caracteres';
        }

        $telefono = is_string($in['telefono'] ?? null) ? trim($in['telefono']) : '';
        if ($telefono !== '' && !preg_match('/^[0-9 +()\-]{6,20}$/', $telefono)) {
            $errores['telefono'] = 'El teléfono no tiene un formato válido';
        }

        // Servicios que ofrece desde el primer día (opcional): lista de ids sin repetir.
        $servicios = [];
        $bruto = $in['servicios'] ?? [];
        if (!is_array($bruto)) {
            $errores['servicios'] = 'La lista de servicios no es válida';
        } else {
            foreach ($bruto as $valor) {
                $id = filter_var($valor, FILTER_VALIDATE_INT);
                if ($id === false || $id < 1) {
                    $errores['servicios'] = 'La lista de servicios no es válida';
                    break;
                }
                $servicios[$id] = $id;
            }
            $servicios = array_values($servicios);
            if (count($servicios) > self::MAX_SERVICIOS_REGISTRO) {
                $errores['servicios'] = 'Puedes elegir como máximo ' . self::MAX_SERVICIOS_REGISTRO . ' servicios';
            }
        }

        return [
            'errores' => $errores,
            'datos' => [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'password' => $password,
                'telefono' => $telefono === '' ? null : $telefono,
                'servicios' => $servicios,
            ],
        ];
    }
}
