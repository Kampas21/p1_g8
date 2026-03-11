<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../../entities/categoria.php';

$categorias = Categoria::getCategorias(true);
?>

<h1>Lista de Categorías</h1>

<p><a href="crearCategoria.php">Nueva Categoría</a></p>

<li>
      <a href="../productos/productosList.php">
        Ver todos los productos
      </a>
</li><br>

<table border="1">
    <tr>
      <th>ID</th>
<th>Nombre</th>
<th>Descripción</th>
<th>Acciones</th>
    </tr>

    <?php foreach($categorias as $cat): ?>
    <tr>
        <td><?= $cat['id'] ?></td>
        <td><?= htmlspecialchars($cat['nombre']) ?></td>
        <td><?= htmlspecialchars($cat['descripcion']) ?></td>
        <td>
            <a href="editarCategoria.php?id=<?= $cat['id'] ?>">Editar</a>
            <a href="../productos/mostrarProductosCategoria.php?id=<?= $cat['id'] ?>">Productos</a>
            <a href="borrarCategoria.php?id=<?= $cat['id'] ?>">Borrar</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<p>
    <a href="../../index.php">Volver al inicio</a>
</p>