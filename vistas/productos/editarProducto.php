<?php
require_once __DIR__ . '/../../entities/producto.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID de producto no válido.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria_id'] ?? '');
    $precio_base = trim($_POST['precio_base'] ?? '');
    $iva = trim($_POST['iva'] ?? '');
    $disponible = trim($_POST['disponible'] ?? '');
    $ofertado = trim($_POST['ofertado'] ?? '');

    if ($nombre !== '' && $descripcion !== '' && $categoria !== '' 
    && $precio_base !== '' && $iva !== '' && $disponible !== '' && $ofertado !== '') {
        Producto::editarProducto((int)$id, $nombre, $descripcion, $categoria, $precio_base, $iva, $disponible, $ofertado);
        header('Location: productosList.php');
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}

$producto = Producto::getProductoById((int)$id);

if (!$producto) {
    die("El producto no existe.");
}

require __DIR__ . '/productosForm.php';