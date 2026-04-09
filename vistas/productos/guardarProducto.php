<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/src/entities/Producto.php';
require_once __DIR__ . '/../../includes/src/services/ProductoService.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

function validarProducto($data) {
    $errores = [];

    if (empty(trim($data['nombre'] ?? ''))) {
        $errores['nombre'] = 'El nombre es obligatorio';
    }

    if (empty(trim($data['descripcion'] ?? ''))) {
        $errores['descripcion'] = 'La descripción es obligatoria';
    }

    $categoria = filter_var($data['categoria_id'], FILTER_VALIDATE_INT);
    if (!$categoria) {
        $errores['categoria_id'] = 'Categoría inválida';
    }

    $precio = filter_var($data['precio'], FILTER_VALIDATE_FLOAT);
    if ($precio === false || $precio < 0) {
        $errores['precio'] = 'Precio inválido';
    }

    $iva = filter_var($data['iva'], FILTER_VALIDATE_INT);
    if (!in_array($iva, [4,10,21])) {
        $errores['iva'] = 'IVA inválido';
    }

    return $errores;
}

$errores = validarProducto($_POST);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!empty($errores)) {
    $_SESSION['errores_producto'] = $errores;

    if ($id) {
        header("Location: ../../editarProducto.php?id=$id");
    } else {
        header("Location: ../../crearProducto.php");
    }
    exit;
}

// DATOS LIMPIOS
$producto = new Producto(
    $id ?: null,
    trim($_POST['nombre']),
    trim($_POST['descripcion']),
    (int)$_POST['categoria_id'],
    (float)$_POST['precio'],
    (int)$_POST['iva'],
    (int)($_POST['disponible'] ?? 1),
    (int)($_POST['ofertado'] ?? 1)
);

// CREAR o EDITAR
if ($id) {
    ProductoService::update($conn, $producto);
} else {
    ProductoService::create($conn, $producto);
}

header("Location: ../../categoriasList.php");
exit;