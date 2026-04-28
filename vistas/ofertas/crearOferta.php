<?php
$tituloPagina = 'Crear oferta';
$rutaCSS = '../../CSS/estilo.css';

ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/ofertaService.php';
require_once __DIR__ . '/../../includes/OfertaProductoDAO.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $descuento = $_POST['descuento'] ?? '';

    //$productos = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];

    $descuento = (float) $descuento;

    if ($descuento < 0 || $descuento > 100) {
        $error = "El descuento debe estar entre 0 y 100.";
    } elseif (
        $nombre !== '' &&
        $fecha_inicio !== '' &&
        $fecha_fin !== '' &&
        $descuento !== ''
    ) {

        $id = OfertaService::crearOferta($nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento);

        foreach ($cantidades as $producto_id => $cantidad) {

            $cantidad = (int)$cantidad;

            if ($cantidad > 0) {
                OfertaProductoDAO::addProducto($id, $producto_id, $cantidad);
            }
        }


        header("Location: " . RUTA_APP . "/vistas/ofertas/listarOfertas.php");
        exit();
    } else {
        $error = "Todos los campos obligatorios deben completarse.";
    }
}

require __DIR__ . '/ofertaForm.php';

$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
