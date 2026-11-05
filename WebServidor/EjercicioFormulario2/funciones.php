<?php

function obtenerRutaArchivo($nombreArchivo) {
    return __DIR__ . DIRECTORY_SEPARATOR . $nombreArchivo;
}

function leerUsuarios() {
    $archivo = obtenerRutaArchivo('users.txt');

    if (!file_exists($archivo)) {
        return [];
    }

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $usuarios = [];

    foreach ($lineas as $linea) {
        $datos = explode(';', $linea);

        if (count($datos) >= 2) {
            $usuarios[] = [
                'usuario' => trim($datos[0]),
                'password' => trim($datos[1])
            ];
        }
    }

    return $usuarios;
}

function validarUsuario($usuario, $password) {
    $usuarios = leerUsuarios();

    foreach ($usuarios as $usuarioGuardado) {
        if ($usuarioGuardado['usuario'] === $usuario && $usuarioGuardado['password'] === $password) {
            return true;
        }
    }

    return false;
}

function registrarUsuario($usuario, $password) {
    $usuario = trim($usuario);

    if ($usuario === '' || trim($password) === '') {
        return false;
    }

    if (strpos($usuario, ';') !== false || strpos($usuario, "\n") !== false || strpos($usuario, "\r") !== false ||
        strpos($password, ';') !== false || strpos($password, "\n") !== false || strpos($password, "\r") !== false) {
        return false;
    }

    foreach (leerUsuarios() as $usuarioGuardado) {
        if ($usuarioGuardado['usuario'] === $usuario) {
            return false;
        }
    }

    $archivo = obtenerRutaArchivo('users.txt');
    $linea = $usuario . ';' . $password . PHP_EOL;

    return file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX) !== false;
}

function registrarLogin($usuario) {
    $archivo = obtenerRutaArchivo('logueados.log');
    $linea = $usuario . '|' . date('Y-m-d H:i:s') . PHP_EOL;

    if (file_exists($archivo) && filesize($archivo) > 0) {
        $contenido = file_get_contents($archivo);

        if (substr($contenido, -1) !== "\n") {
            $linea = PHP_EOL . $linea;
        }
    }

    file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
}

function requiereLogin() {
    session_start();

    if (!isset($_SESSION['login_ok']) || $_SESSION['login_ok'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function cerrarSesion() {
    session_start();
    session_unset();
    session_destroy();
}
?>
