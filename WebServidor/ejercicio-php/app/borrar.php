<?php
require_once 'producto.php';
require_once 'auth.php';
Auth::requerir();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto = Producto::buscar((int)$_POST['id']);
    if ($producto) {
        $producto->borrar();
    }
}
header('Location: index.php');
exit;