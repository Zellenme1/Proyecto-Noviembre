<?php
require_once 'funciones.php';
requiereLogin();

echo '<h1>Bienvenido, ' . htmlspecialchars($_SESSION['usuario']) . '</h1>';
echo '<p>Has entrado correctamente al sistema.</p>';
echo '<p><a href="privado.php">Ir a la página privada</a></p>';
echo '<a href="logout.php">Cerrar sesión</a>';
?>
