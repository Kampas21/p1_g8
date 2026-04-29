<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';

$user = require_login();

$ofertas_seleccionadas = $_POST['ofertas'] ?? [];

if (!PedidoService::carritoTieneProductos()) {
    header("Location: ../../vistas/pedidos/carrito.php");
    exit;
}

PedidoService::limpiarOfertasCarrito();

$pedido_productos = [];
$precio_sin_descuento = 0;

foreach (PedidoService::getCarritoItems() as $id => $item) {
    $cantidad = (int)($item['cantidad'] ?? 0);
    $precio = (float)($item['precio_unitario'] ?? 0);

    $pedido_productos[$id] = $cantidad;
    $precio_sin_descuento += $precio * $cantidad;
}

$errores_ofertas = [];


$total_descuento = 0;

foreach ($ofertas_seleccionadas as $oferta_id) {

    $oferta = OfertaDAO::getById($oferta_id);
    $productos_oferta = ProductoDAO::getProductosDeOferta($oferta_id);

    if (!$oferta) {
        $errores_ofertas[] = "La oferta $oferta_id no existe";
        continue;
    }

    $veces = PHP_INT_MAX;
    $motivo = "";

    // 🔹 comprobar si se puede aplicar
    foreach ($productos_oferta as $po) {

        $id = $po->getId();
        $req = $po->cantidad;

        if (!isset($pedido_productos[$id])) {
            $veces = 0;
            $motivo = "Faltan productos para la oferta '{$oferta->getNombre()}'";
            break;
        }

        $disponible = intdiv($pedido_productos[$id], $req);
        $veces = min($veces, $disponible);
    }

    if ($veces <= 0) {
        $errores_ofertas[] = $motivo ?: "No se puede aplicar la oferta '{$oferta->getNombre()}'";
        continue;
    }


    $precio_pack = 0;

    foreach ($productos_oferta as $po) {
        $precio_pack += $po->getPrecioFinal() * $po->cantidad;
    }

    $descuento_unitario = $precio_pack * ($oferta->getDescuento() / 100);
    $descuento_total_oferta = $descuento_unitario * $veces;

    $total_descuento += $descuento_total_oferta;

    PedidoService::agregarOfertaAlCarrito(
        (int)$oferta_id,
        $oferta->getNombre(),
        $veces,
        $descuento_total_oferta
    );


    foreach ($productos_oferta as $po) {
        $id = $po->getId();
        $pedido_productos[$id] -= $po->cantidad * $veces;
    }
}


$_SESSION['errores_ofertas'] = $errores_ofertas;

header("Location: ../../vistas/pedidos/carrito.php");
exit;