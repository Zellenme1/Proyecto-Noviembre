<?php
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/sesion.php';

abrirSesion();

if (existeSesion('login_ok') && $_SESSION['login_ok'] === true) {
    header('Location: loading.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (validarUsuario($usuario, $password)) {
        session_regenerate_id(true);
        crearSesion('usuario', $usuario);
        crearSesion('login_ok', true);
        registrarLogin($usuario);
        header('Location: loading.php');
        exit;
    }

    $error = 'Usuario o contraseña incorrectos';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
</head>
<body>
    <h2>Iniciar sesión</h2>

    <?php if ($error !== ''): ?>
        <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST">
        Usuario: <input type="text" name="usuario" required><br><br>
        Contraseña: <input type="password" name="password" required><br><br>
        <input type="submit" value="Entrar">
    </form>

    <p><a href="registro.php">Crear una cuenta</a></p>
</body>
</html>