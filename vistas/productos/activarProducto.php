<?php
require_once __DIR__ . '/../../entities/producto.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID de produto no válido.");
}

$producto = Producto::getProductoById((int)$id);

if (!$producto) {
    die("El producto no existe.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Producto::activarProducto((int)$id);
    header('Location: productosList.php');
    exit();
}
?>

<h1>Activar producto</h1>

<p>¿Seguro que quieres volver a poner este producto en la carta <strong><?= htmlspecialchars($producto['nombre']) ?></strong>?</p>

<form method="POST">
    <button type="submit">Sí, activar</button>
</form>

<p>
    <a href="productosList.php">Cancelar</a>
</p>