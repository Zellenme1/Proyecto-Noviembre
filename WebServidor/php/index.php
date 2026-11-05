<?php
$host = "mysql-server";
$user = "usuario_app";
$password = "pass_app";
$database = "mi_base_datos";

$conexion = @new mysqli($host, $user, $password, $database);

if ($conexion-> connect_error){
    echo "<h1> ❌Error de conexión: </h1>";
    echo "<p>" . $conexion->connect_error . "</p>";
} else {
    echo "<h1> ✅Conexión exitosa</h1>";
    echo "<p>Se ha conectado correcatamente al contenedor <strong>$host</strong> desde PHP.</p>";
}
?>