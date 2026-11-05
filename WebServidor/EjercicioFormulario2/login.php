<?php
require_once 'funciones.php';

session_start();

if (isset($_SESSION['login_ok']) && $_SESSION['login_ok'] === true) {
    header('Location: loading.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (validarUsuario($usuario, $password)) {
        $_SESSION['usuario'] = $usuario;
        $_SESSION['login_ok'] = true;
        registrarLogin($usuario);
        header('Location: loading.php');
        exit;
    }

    $error = 'Usuario o contraseña incorrectos';
}

echo "<h2>Iniciar sesión</h2>";

if ($error !== '') {
    echo "<p style='color:red;'>$error</p>";
}

echo "
<form method='POST'>
    Usuario: <input type='text' name='usuario' required><br><br>
    Contraseña: <input type='password' name='password' required><br><br>
    <input type='submit' value='Entrar'>
</form>
<p><a href='registro.php'>Crear una cuenta</a></p>
";
