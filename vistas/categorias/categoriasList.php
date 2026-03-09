<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../../entities/categoria.php';

$categorias = Categoria::getCategorias();
?>

<h1>Lista de Categorías</h1>

<a href="crearCategoria.php">Nueva Categoría</a>

<table border="1">

<tr>
<th>Nombre</th>
<th>Descripción</th>
<th>Acciones</th>
</tr>

<?php foreach($categorias as $cat): ?>

<tr>

<td><?= $cat['nombre'] ?></td>
<td><?= $cat['descripcion'] ?></td>

<td>
<a href="editarCategoria.php?id=<?= $cat['id'] ?>">Editar</a>
<a href="borrarCategoria.php?id=<?= $cat['id'] ?>">Borrar</a>
</td>

</tr>

<?php endforeach; ?>

</table>