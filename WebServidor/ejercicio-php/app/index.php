<?php
require_once 'producto.php';
require_once 'auth.php';
Auth::requerir();
$productos = Producto::todos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos</title>
</head>
<body>
  <h1>Productos</h1>
  <form action="logout.php" method="POST" style="display:inline; float:right">
  Hola, <?= htmlspecialchars(Auth::nombre()) ?>
  <button type="submit">Cerrar sesión</button>
</form>
  <a href="edit.php"><button>Nuevo producto</button></a>

  <table border="1" cellpadding="8">
    <tr>
      <th>Nombre</th>
      <th>Precio</th>
      <th>Acciones</th>
    </tr>
    <?php foreach ($productos as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p->nombre) ?></td>
        <td><?= number_format($p->precio, 2) ?> €</td>
        <td>
          <a href="edit.php?id=<?= $p->id ?>"><button>Editar</button></a>
          <form action="borrar.php" method="POST" style="display:inline"
                onsubmit="return confirm('¿Seguro que quieres borrar este producto?')">
            <input type="hidden" name="id" value="<?= $p->id ?>">
            <button type="submit">Borrar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>