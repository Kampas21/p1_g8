<?php
require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../entities/categoria.php';

$id = $_GET['id'] ?? null;
$categoria_id = $_GET['categoria_id'] ?? null;


if (!$id || !is_numeric($id)) {
    echo '<p>ID de produto no válido.</p>';
    echo '<a class="btn-volver" href="mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit();
}

if (!$categoria_id) {
    echo '<p>No se especificó la categoría correctamente.</p>';
    echo '<a class="btn-volver" href="mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit();
}

$producto = Producto::getProductoById((int)$id);
$categoria_activa=Categoria::getCategoriaById($categoria_id);

if (!$producto) {
    echo '<p>El producto no existe.</p>';
    echo '<a class="btn-volver" href="mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit();
}

if ($producto['ofertado']) {
    echo '<p>El producto ya está en la carta, no puedes añadir el producto.</p>';
    echo '<a class="btn-volver" href="mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit();
}

if (!$categoria_activa['activa']) {
    echo '<p>La categoría no está activa, no puedes ofertar el producto.</p>';
    echo '<a class="btn-volver" href="mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Producto::activarProducto((int)$id);
    header("Location: mostrarProductosCategoria.php?id=$categoria_id");
    exit();
}
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1>Activar <?= htmlspecialchars($producto['nombre']) ?></h1>

<p>¿Seguro que quieres volver a poner este producto en la carta <strong><?= htmlspecialchars($producto['nombre']) ?></strong>?</p>

<form method="POST">
    <p><button type="submit" class="btn-aceptar">Sí, activar</button></p>
</form>

<p>
    <a class="btn-volver" href="mostrarProductosCategoria.php?id=<?= $categoria_id?>">
        Cancelar
    </a>
</p>