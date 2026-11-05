<?php
if (isset($_COOKIE['visitas'])) {
    $visitas = $_COOKIE['visitas'];
    $visitas = $visitas + 1;
} else {
    $visitas = 1;
}

setcookie('visitas', $visitas, time() + 3600);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visitas</title>
</head>
<body>
    <p>Has visitado esta página <?= $visitas ?> veces.</p>
</body>
</html>