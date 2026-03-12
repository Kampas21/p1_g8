<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../entities/categoria.php';

$categoria_id = $_GET['id'] ?? null;

if (!$categoria_id || !is_numeric($categoria_id)) {
    echo '<p>No se especificó la categoría correctamente.</p>';
    echo '<a class="btn-volver" href="mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit();
}

$productos = Producto::getProductosPorCategoria($categoria_id);
$nombreCategoria = Categoria::getCategoriaById($categoria_id);

?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1>Lista de <?= htmlspecialchars($nombreCategoria['nombre']) ?> </h1>

<p><a class="btn-nuevo" href="crearProducto.php?id=<?= $categoria_id ?>">Nuevo Producto</a></p>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Categoria</th>
        <th>Precio base</th>
        <th>IVA</th>
        <th>Precio final</th>
        <th>Disponible</th>
        <th>Ofertado</th>
        <th>Imagen</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($productos as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= htmlspecialchars($cat['nombre']) ?></td>
            <td class="descripcion"><?= htmlspecialchars($cat['descripcion']) ?></td>
            <td><?= htmlspecialchars($cat['categoria_nombre']) ?></td>
            <td><?= htmlspecialchars($cat['precio_base']) ?></td>
            <td><?= htmlspecialchars($cat['iva']) ?></td>
            <td><?= Producto::getPrecioFinal($cat['precio_base'], $cat['iva']); ?></td>
            <td><?= $cat['disponible'] > 0
                    ? '<span style="color:green">Disponible (' . $cat['disponible'] . ')</span>'
                    : '<span style="color:red">No disponible</span>' ?>
            </td>
            <td><?= $cat['ofertado']
                    ? '<span style="color:green">Activo</span>'
                    : '<span style="color:red">Inactivo</span>' ?></td>
            <td>
                <?php if (!empty($cat['imagen'])): ?>
                    <img src=<?= htmlspecialchars($cat['imagen']) ?> alt="Imagen de <?= htmlspecialchars($cat['nombre']) ?>" width="100">
                <?php else: ?>
                    No hay imagen
                <?php endif; ?>
            </td>
            <td>
                <a class="btn editar" href="editarProducto.php?id=<?= $cat['id'] ?>">Editar</a>
                <a class="btn activar" href="activarProducto.php?id=<?= $cat['id'] ?>&categoria_id=<?= $categoria_id ?>">Activar</a>
                <a class="btn borrar" href="borrarProducto.php?id=<?= $cat['id'] ?>&categoria_id=<?= $categoria_id ?>">Borrar</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<p>
    <a class="btn-volver" href="../categorias/categoriasList.php">Volver</a>
</p>