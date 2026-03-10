<?php
require_once __DIR__ . '/../../entities/producto.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria_id'] ?? '');
    $precio_base = trim($_POST['precio_base'] ?? '');
    $iva = trim($_POST['iva'] ?? '');
    $disponible = trim($_POST['disponible'] ?? '');
    $ofertado = trim($_POST['ofertado'] ?? '');

    $categoria = (int) $categoria;
    $precio_base = (float) $precio_base;
    $iva = (int) $iva;
    $disponible = (int) $disponible;
    $ofertado = (int) $ofertado;

    // Validación extra
    if ($precio_base < 0 || $iva < 0 || $disponible < 0 || $ofertado < 0) {
        $error = "Valores numéricos no pueden ser negativos.";
    } elseif (
        $nombre !== '' && $descripcion !== '' && $categoria !== ''
        && $precio_base !== '' && $iva !== '' && $disponible !== '' && $ofertado !== ''
    ) {
        Producto::crearProducto($nombre, $descripcion, $categoria, $precio_base, $iva, $disponible, $ofertado);
        header('Location: productosList.php');
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}

require __DIR__ . '/productosForm.php';
