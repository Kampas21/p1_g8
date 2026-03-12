<?php
$tituloPagina = 'Crear producto';
$rutaCSS = '../../CSS/estilo.css';


ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../entities/producto.php';

$categoria_id = $_GET['id'] ?? null;

// $desdeCategoria = isset($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    //$categoria_id = trim($_POST['categoria_id'] ?? '');
    $precio_base = trim($_POST['precio_base'] ?? '');
    $iva = trim($_POST['iva'] ?? '');
    $disponible = trim($_POST['disponible'] ?? '');
    $ofertado = trim($_POST['ofertado'] ?? '');

    $precio_base = (float) $precio_base;
    $disponible = (int) $disponible;

    if ($precio_base < 0 || $disponible < 0) {
        $error = "Valores numéricos no pueden ser negativos.";
    } elseif (
        $nombre !== '' && $descripcion !== '' && $categoria_id !== ''
        && $precio_base !== '' && $iva !== '' && $disponible !== '' && $ofertado !== ''
    ) {
        $imagenPath = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreArchivo = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $carpetaDestino = __DIR__ . '/../../img/img_productos/';

            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            // Generar nombre único
            $nombreUnico = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $nombreArchivo);
            $destinoFinal = $carpetaDestino . $nombreUnico;

            if (move_uploaded_file($temporal, $destinoFinal)) {
                $imagenPath = '/p1_g8/img/img_productos/' . $nombreUnico;
            } else {
                $error = "Error al subir la imagen.";
            }
        }

        Producto::crearProducto($nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado, $imagenPath);
        // if ($desdeCategoria) {
        //     header("Location: mostrarProductosCategoria.php?id=$categoria_id");
        // } else {
        header("Location: mostrarProductosCategoria.php?id=$categoria_id");
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}

require __DIR__ . '/productosForm.php';



$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';