<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../includes/ofertaService.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/ofertaEnPedidoService.php';

$pedido_id = $_POST['pedido_id'] ?? null;
$ofertas_seleccionadas = $_POST['ofertas'] ?? [];

if (!$pedido_id) {
    die("Pedido no válido");
}

//////////////////////////////////////////////////////
// 🔹 1. Productos del pedido
//////////////////////////////////////////////////////

$productos = PedidoService::getProductosPedido($pedido_id);

$pedido_productos = [];
$precio_sin_descuento = 0;

foreach ($productos as $p) {
    $id = $p->getProductoId();
    $cantidad = $p->getCantidad();
    $precio = $p->getPrecio();

    $pedido_productos[$id] = $cantidad;
    $precio_sin_descuento += $precio * $cantidad;
}

//////////////////////////////////////////////////////
// 🔹 2. Limpiar ofertas anteriores
//////////////////////////////////////////////////////

PedidoService::limpiarOfertas($pedido_id);

//////////////////////////////////////////////////////
// 🔥 NUEVO: errores de ofertas
//////////////////////////////////////////////////////

$errores_ofertas = [];

//////////////////////////////////////////////////////
// 🔹 3. Aplicar ofertas
//////////////////////////////////////////////////////

$total_descuento = 0;

foreach ($ofertas_seleccionadas as $oferta_id) {

    $oferta = OfertaService::getById($oferta_id);
    $productos_oferta = ProductoService::getProductosDeOferta($oferta_id);

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

    //////////////////////////////////////////////////////
    // 🔹 4. Calcular descuento
    //////////////////////////////////////////////////////

    $precio_pack = 0;

    foreach ($productos_oferta as $po) {
        $precio_pack += $po->getPrecioFinal() * $po->cantidad;
    }

    $descuento_unitario = $precio_pack * ($oferta->getDescuento() / 100);
    $descuento_total_oferta = $descuento_unitario * $veces;

    $total_descuento += $descuento_total_oferta;

    //////////////////////////////////////////////////////
    // 🔹 5. Guardar oferta aplicada
    //////////////////////////////////////////////////////

    OfertaEnPedidoService::addOferta(
        $pedido_id,
        $oferta_id,
        $veces,
        $descuento_total_oferta
    );

    //////////////////////////////////////////////////////
    // 🔹 6. Restar productos
    //////////////////////////////////////////////////////

    foreach ($productos_oferta as $po) {
        $id = $po->getId();
        $pedido_productos[$id] -= $po->cantidad * $veces;
    }
}

//////////////////////////////////////////////////////
// 🔹 7. Guardar totales
//////////////////////////////////////////////////////

PedidoService::actualizarTotales(
    $pedido_id,
    $precio_sin_descuento,
    $total_descuento
);

//////////////////////////////////////////////////////
// 🔥 8. REDIRECCIÓN CON POPUP
//////////////////////////////////////////////////////

$_SESSION['errores_ofertas'] = $errores_ofertas;

header("Location: ../../vistas/pedidos/carrito.php");
exit;