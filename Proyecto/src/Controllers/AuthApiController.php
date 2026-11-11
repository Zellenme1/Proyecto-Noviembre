<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\DAO\UsuarioDAO;
use App\Models\Usuario;

/**
 * Endpoints: POST /api/login, POST /api/logout, POST /api/registro, GET /api/me
 */
class AuthApiController extends ApiController
{
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
            'csrf_token' => Csrf::token(),
            'redirect' => $usuario->getRol() === Usuario::ROL_ADMIN ? '/servicios-spa' : '/',
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->jsonResponse(['message' => 'Sesión cerrada']);
    }

    public function me(): void
    {
        $this->jsonResponse(['usuario' => Auth::requireLogin()]);
    }

    public function registro(): void
    {
        $in = $this->readJsonBody();
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

        if (!isset($errores['email']) && $this->usuarioDAO->emailExists($email)) {
            $this->jsonResponse(['error' => 'Ya existe una cuenta con ese email', 'errores' => ['email' => 'Email ya registrado']], 409);
        }

        if ($errores) {
            $this->jsonResponse(['error' => 'Datos no válidos', 'errores' => $errores], 422);
        }

        // El rol y los puntos de bienvenida los fija SIEMPRE el servidor:
        // nada de lo que envíe el cliente puede crear un ADMIN ni cambiar el saldo.
        $nuevo = new Usuario(
            null,
            $nombre,
            $apellidos,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $telefono === '' ? null : $telefono,
            Usuario::ROL_PROFESIONAL,
            Usuario::PUNTOS_BIENVENIDA
        );

        try {
            $id = $this->usuarioDAO->create($nuevo);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') { // email duplicado por carrera entre dos registros
                $this->jsonResponse(['error' => 'Ya existe una cuenta con ese email'], 409);
            }
            throw $e;
        }

        $this->jsonResponse(['usuario' => $this->usuarioDAO->getById($id)], 201);
    }
}
