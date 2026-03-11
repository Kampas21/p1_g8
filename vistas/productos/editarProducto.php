<?php
require_once __DIR__ . '/../../entities/producto.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID de producto no válido.");
}

$producto = Producto::getProductoById((int)$id);

if (!$producto) {
    die("El producto no existe.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria_id'] ?? '');
    $precio_base = trim($_POST['precio_base'] ?? '');
    $iva = trim($_POST['iva'] ?? '');
    $disponible = trim($_POST['disponible'] ?? '');
    $ofertado = trim($_POST['ofertado'] ?? '');

    $precio_base = (float) $precio_base;
    $disponible = (int) $disponible;

    if ($precio_base < 0 || $disponible < 0) {
        $error = "Valores numéricos no pueden ser negativos.";
    } elseif (
        $nombre !== '' && $descripcion !== '' && $categoria !== ''
        && $precio_base !== '' && $iva !== '' && $disponible !== '' && $ofertado !== ''
    ) {
        $imagenPath = $producto['imagen'];
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreArchivo = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $carpetaDestino = __DIR__ . '/../../img/img_productos/';

            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            $nombreUnico =  time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $nombreArchivo);
            $destinoFinal = $carpetaDestino . $nombreUnico;

            if (move_uploaded_file($temporal, $destinoFinal)) {
                $imagenPath = '/p1_g8/img/img_productos/' . $nombreUnico;
            } else {
                $error = "Error al subir la imagen.";
            }
        }

        Producto::editarProducto((int)$id, $nombre, $descripcion, $categoria, $precio_base, $iva, $disponible, $ofertado, $imagenPath);
        header('Location: productosList.php');
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}


require __DIR__ . '/productosForm.php';
