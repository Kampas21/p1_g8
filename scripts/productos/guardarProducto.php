<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

/* =========================
   1. RECOGER DATOS LIMPIOS
========================= */

$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
$categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
$precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
$iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);
$se_cocina = isset($_POST['se_cocina']) ? 1 : 0;


$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

/* =========================
   2. VALIDACIÓN
========================= */

$errores = [];

if (!$nombre || strlen(trim($nombre)) < 3) {
    $errores['nombre'] = 'El nombre es obligatorio (mín 3 caracteres)';
}

if (!$descripcion) {
    $errores['descripcion'] = 'La descripción es obligatoria';
}

if (!$categoria_id) {
    $errores['categoria_id'] = 'Categoría inválida';
}

if ($precio === false || $precio < 0) {
    $errores['precio'] = 'Precio inválido';
}

if (!in_array($iva, [4, 10, 21])) {
    $errores['iva'] = 'IVA inválido';
}

/* =========================
   3. SI HAY ERRORES
========================= */

if (!empty($errores)) {
    $_SESSION['errores_producto'] = $errores;

    if ($id) {
        header("Location: ../../vistas/productos/editarProducto.php?id=$id");
    } else {
        header("Location: ../../vistas/productos/crearProducto.php");
    }
    exit;
}


/* =========================
   4. GUARDAR
========================= */

if ($id) {
    ProductoDAO::update($id, $nombre, $descripcion, $categoria_id, $precio, $iva, $se_cocina);
} else {
    ProductoDAO::create($nombre, $descripcion, $categoria_id, $precio, $iva, $se_cocina);
}

/* =========================
   5. REDIRECCIÓN
========================= */

header("Location: ../../vistas/categorias/categoriasList.php");
exit;