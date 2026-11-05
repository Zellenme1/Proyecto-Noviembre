<?php
require_once 'producto.php';
require_once 'auth.php';
Auth::requerir();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $producto = new Producto($id, trim($_POST['nombre']), (float)$_POST['precio']);
    $producto->guardar();
    header('Location: index.php');
    exit;
}

$producto = isset($_GET['id']) ? Producto::buscar((int)$_GET['id']) : new Producto();
if ($producto === null) {
    header('Location: index.php');
    exit;
}
$esNuevo = $producto->id === null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $esNuevo ? 'Nuevo' : 'Editar' ?> producto</title>
</head>
<body>
  <h1><?= $esNuevo ? 'Nuevo producto' : 'Editar producto' ?></h1>

  <form method="POST">
    <input type="hidden" name="id" value="<?= $producto->id ?>">
    <p>
      <label>Nombre:
        <input type="text" name="nombre" required
               value="<?= htmlspecialchars($producto->nombre) ?>">
      </label>
    </p>
    <p>
      <label>Precio:
        <input type="number" name="precio" step="0.01" min="0" required
               value="<?= $producto->precio ?>">
      </label>
    </p>
    <button type="submit">Guardar</button>
    <a href="index.php">Cancelar</a>
  </form>
</body>
</html>