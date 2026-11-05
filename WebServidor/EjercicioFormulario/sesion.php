<?php

function abrirSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function existeSesion($clave) {
    return isset($_SESSION[$clave]);
}

function crearSesion($clave, $valor) {
    $_SESSION[$clave] = $valor;
}

function borrarSesion($clave) {
    unset($_SESSION[$clave]);
}