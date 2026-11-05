<?php
$host = getenv('DB_HOST');
$name = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
    echo "<h1>Conexión exitosa</h1>";
} catch (PDOException $e) {
    echo "<h1>Error</h1>" . $e->getMessage();
}