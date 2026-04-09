<?php
session_start();

require_once __DIR__ . '/../../entities/Producto.php';
require_once __DIR__ . '/../../includes/productoService.php';

function validarProducto($data) {
    $errores = [];

    $nombre = trim($data['nombre'] ?? '');
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre es obligatorio';
    }

    $descripcion = trim($data['descripcion'] ?? '');
    if (empty($descripcion)) {
        $errores['descripcion'] = 'La descripción es obligatoria';
    }

    $precio = filter_var($data['precio'], FILTER_VALIDATE_FLOAT);
    if ($precio === false || $precio < 0) {
        $errores['precio'] = 'Precio inválido';
    }

    $iva = filter_var($data['iva'], FILTER_VALIDATE_INT);
    if (!in_array($iva, [4, 10, 21])) {
        $errores['iva'] = 'IVA inválido';
    }

    $categoria = filter_var($data['categoria_id'], FILTER_VALIDATE_INT);
    if ($categoria === false) {
        $errores['categoria_id'] = 'Categoría inválida';
    }

    // IMPORTANTE
    $disponible = isset($data['disponible']) ? 1 : 0;
    $ofertado = isset($data['ofertado']) ? 1 : 0;

    return [$errores, $disponible, $ofertado];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    list($errores, $disponible, $ofertado) = validarProducto($_POST);

    if (!empty($errores)) {
        $_SESSION['errores_producto'] = $errores;
        $_SESSION['old_producto'] = $_POST;
        header("Location: ../../vistas/productos/productoForm.php");
        exit;
    }

    $producto = new Producto(
        $_POST['id'] ?? null,
        $_POST['nombre'],
        $_POST['descripcion'],
        $_POST['categoria_id'],
        $_POST['precio'],
        $_POST['iva'],
        $disponible,
        $ofertado
    );

    if (!empty($_POST['id'])) {
        ProductoService::update($producto);
    } else {
        ProductoService::create($producto);
    }

    header("Location: ../../vistas/productos/mostrarProductosCategoria.php?categoria_id=" . $_POST['categoria_id']);
    exit;
}