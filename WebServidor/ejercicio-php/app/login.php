<?php 
require_once 'usuario.php';
require_once 'auth.php';

if (Auth::estaLogueado()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = Usuario::autenticar(trim($_POST['nombre']), $_POST['password']);
    if ($u) {
        Auth::entrar($u);
        header('Location index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion</title>
</head>
<body>
    <h1>Iniciar sesion</h1>

    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <P>
            <label>Usuario:
                <input type="text" name="nombre" required autofocus>
            </label>
        </P>
        <p>
            <label>Contraseña:
                <input type="password" name="password" required>
            </label>
        </p>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>