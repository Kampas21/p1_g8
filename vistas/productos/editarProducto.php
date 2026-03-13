<?php
$tituloPagina = 'Editar Producto';
$rutaCSS = '../../CSS/estilo.css';
ob_start();

require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../includes/config.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo '<p>ID de produto no válido.</p>';
    echo '<a class="btn-volver" href="' . RUTA_APP . '/vistas/productos/mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit();
}

$producto = Producto::getProductoById((int)$id);

if (!$producto) {
    echo '<p>El producto no existe.</p>';
    echo '<a class="btn-volver" href="' . RUTA_APP . '/vistas/productos/mostrarProductosCategoria.php?id=' . $categoria_id . '">Volver</a>';
    exit();
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
             
        $imagenPath = 'img/img_productos/' . $nombreUnico;            }
         else {
                $error = "Error al subir la imagen.";
            }
        }

        Producto::editarProducto((int)$id, $nombre, $descripcion, $categoria, $precio_base, $iva, $disponible, $ofertado, $imagenPath);
        header("Location: " . RUTA_APP . "/vistas/productos/mostrarProductosCategoria.php?id=$categoria");
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}


require __DIR__ . '/productosForm.php';


$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';