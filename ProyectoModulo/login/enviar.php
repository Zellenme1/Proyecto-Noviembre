<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos enviados</title>
</head>
<body>
    <h1>Datos enviados</h1>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = htmlspecialchars($_POST['nombre'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $mensaje = htmlspecialchars($_POST['mensaje'] ?? '');

        echo "<p><strong>Nombre:</strong> $nombre</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Mensaje:</strong> $mensaje</p>";
    } else {
        echo "<p>No se enviaron datos.</p>";
    }
    ?>

    <p><a href="index.php">Volver</a></p>
</body>
</html>
