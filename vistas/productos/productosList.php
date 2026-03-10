<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../entities/producto.php';

$productos = Producto::getProductos();
?>

<h1>Lista de Productos</h1>

<p><a href="crearProducto.php">Nuevo Producto</a></p>

<table border="1">
    <tr>
      <th>ID</th>
<th>Nombre</th>
<th>Descripción</th>
<th>Categoria</th>
<th>Precio base</th>
<th>IVA</th>
<th>Disponible</th>
<th>Ofertado</th>
<th>Acciones</th>
    </tr>

    <?php foreach($productos as $cat): ?>
    <tr>
        <td><?= $cat['id'] ?></td>
        <td><?= htmlspecialchars($cat['nombre']) ?></td>
        <td><?= htmlspecialchars($cat['descripcion']) ?></td>
        <td><?= htmlspecialchars($cat['categoria_id']) ?></td>
        <td><?= htmlspecialchars($cat['precio_base']) ?></td>
        <td><?= htmlspecialchars($cat['iva']) ?></td>
        <td><?= htmlspecialchars($cat['disponible']) ?></td>
        <td><?= htmlspecialchars($cat['ofertado']) ?></td>
        <td>
            <a href="editarProducto.php?id=<?= $cat['id'] ?>">Editar</a>
            <a href="borrarProducto.php?id=<?= $cat['id'] ?>">Borrar</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<p>
    <a href="../../index.html">Volver al inicio</a>
</p>