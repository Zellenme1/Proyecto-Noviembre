<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\DAO\UsuarioDAO;

/**
 * POST /api/foto  (multipart/form-data, campo "foto")
 * Guarda la foto de perfil del usuario con sesión iniciada.
 */
class PerfilApiController extends ApiController
{
    private const MAX_BYTES = 2 * 1024 * 1024; // 2 MB
    private const MAX_LADO = 4000;             // píxeles por lado
    private const CARPETA_PUBLICA = '/uploads/avatars/';

    // Tipos admitidos. La extensión sale de aquí, nunca del nombre que envía el cliente.
    private const TIPOS = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

    private UsuarioDAO $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function subirFoto(): void
    {
        $usuario = Auth::requireLogin();

        $archivo = $_FILES['foto'] ?? null;
        $codigo = is_array($archivo) ? (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
        if ($codigo !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['error' => $this->mensajeDeSubida($codigo)], 422);
        }
        if ($archivo['size'] > self::MAX_BYTES) {
            $this->jsonResponse(['error' => 'La foto no puede superar los 2 MB'], 422);
        }
        if (!is_uploaded_file($archivo['tmp_name'])) {
            $this->jsonResponse(['error' => 'Archivo no válido'], 400);
        }

        // Se comprueba el contenido real del archivo, no su nombre ni el tipo que declara el navegador.
        $info = @getimagesize($archivo['tmp_name']);
        if ($info === false || !isset(self::TIPOS[$info[2]])) {
            $this->jsonResponse(['error' => 'Solo se admiten imágenes JPG, PNG o WEBP'], 422);
        }
        if ($info[0] > self::MAX_LADO || $info[1] > self::MAX_LADO) {
            $this->jsonResponse(['error' => 'La imagen es demasiado grande (máximo 4000 píxeles por lado)'], 422);
        }

        $carpeta = dirname(__DIR__, 2) . '/public' . self::CARPETA_PUBLICA;
        if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
            error_log('No se pudo crear la carpeta de fotos: ' . $carpeta);
            $this->jsonResponse(['error' => 'No se pudo guardar la foto'], 500);
        }

        $nombre = 'u' . $usuario->getId() . '_' . bin2hex(random_bytes(8)) . '.' . self::TIPOS[$info[2]];
        if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombre)) {
            error_log('No se pudo mover la foto subida a ' . $carpeta . $nombre);
            $this->jsonResponse(['error' => 'No se pudo guardar la foto'], 500);
        }

        $anterior = $usuario->getFoto();
        $this->usuarioDAO->actualizarFoto((int)$usuario->getId(), self::CARPETA_PUBLICA . $nombre);

        // Se borra la foto anterior para no acumular archivos huérfanos.
        if ($anterior !== null && str_starts_with($anterior, self::CARPETA_PUBLICA)) {
            @unlink($carpeta . basename($anterior));
        }

        $this->jsonResponse(['foto' => self::CARPETA_PUBLICA . $nombre], 201);
    }

    private function mensajeDeSubida(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'La foto es demasiado grande (máximo 2 MB)',
            UPLOAD_ERR_NO_FILE => 'No se ha seleccionado ninguna foto',
            default => 'No se pudo subir la foto',
        };
    }

    public function actualizar(): void
    {
        $usuario = Auth::requireLogin();
        $in = $this->readJsonBody();

        $nombre = is_string($in['nombre'] ?? null) ? trim($in['nombre']) : '';
        $apellidos = is_string($in['apellidos'] ?? null) ? trim($in['apellidos']) : '';
        $telefono = is_string($in['telefono'] ?? null) ? trim($in['telefono']) : '';

        $errores = [];
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $errores['nombre'] = 'El nombre es obligatorio (máximo 100 caracteres)';
        }
        if ($apellidos === '' || mb_strlen($apellidos) > 100) {
            $errores['apellidos'] = 'Los apellidos son obligatorios (máximo 100 caracteres)';
        }
        if ($telefono !== '' && !preg_match('/^[0-9 +()\-]{6,20}$/', $telefono)) {
            $errores['telefono'] = 'El teléfono no tiene un formato válido';
        }
        if ($errores) {
            $this->jsonResponse(['error' => 'Datos no válidos', 'errores' => $errores], 422);
        }

        $id = (int)$usuario->getId();
        $this->usuarioDAO->actualizarDatos($id, $nombre, $apellidos, $telefono === '' ? null : $telefono);

        $this->jsonResponse(['usuario' => $this->usuarioDAO->getById($id)]);
    }
}
