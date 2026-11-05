<?php
require_once 'funciones.php';

session_start();

if (isset($_SESSION['login_ok']) && $_SESSION['login_ok'] === true) {
    header('Location: loading.php');
    exit;
}

$error = '';
$registrado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (registrarUsuario($usuario, $password)) {
        $registrado = true;
    } else {
        $error = 'No se pudo registrar. Comprueba los datos o usa otro usuario.';
    }
}

echo "<h2>Crear una cuenta</h2>";

if ($registrado) {
    echo "<p>Usuario registrado correctamente. <a href='login.php'>Iniciar sesión</a></p>";
} else {
    if ($error !== '') {
        echo "<p style='color:red;'>$error</p>";
    }

    echo "
    <form method='POST'>
        Usuario: <input type='text' name='usuario' required><br><br>
        Contraseña: <input type='password' name='password' required><br><br>
        <input type='submit' value='Registrarse'>
    </form>
    <p><a href='login.php'>Volver al login</a></p>
    ";
}
?>