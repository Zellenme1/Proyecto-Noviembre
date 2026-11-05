<?php
require_once 'funciones.php';
requiereLogin();

echo '<h1>Zona privada</h1>';
echo '<p>Bienvenido, ' . htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8') . '.</p>';
echo '<p>Has iniciado sesión correctamente.</p>';
echo '<a href="loading.php">Volver</a> | <a href="logout.php">Cerrar sesión</a>';
?>
