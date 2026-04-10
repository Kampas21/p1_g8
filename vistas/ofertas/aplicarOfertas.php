<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/ofertaService.php';
require_once __DIR__ . '/../../includes/productoService.php';

$pedido_id = $_POST['pedido_id'];
$ofertas_seleccionadas = $_POST['ofertas'] ?? [];

//////////////////////////////////////////////////////
// 🔹 1. Obtener productos del pedido
//////////////////////////////////////////////////////

$productos = Pedido::getProductos($pedido_id);

$pedido_productos = [];
$precio_sin_descuento = 0;

foreach ($productos as $p) {
    $pedido_productos[$p['producto_id']] = $p['cantidad'];
    $precio_sin_descuento += $p['precio_unitario'] * $p['cantidad'];
}

//////////////////////////////////////////////////////
// 🔹 2. Limpiar ofertas previas (opcional pero recomendado)
//////////////////////////////////////////////////////

Pedido::limpiarOfertas($pedido_id);

//////////////////////////////////////////////////////
// 🔹 3. Aplicar ofertas
//////////////////////////////////////////////////////

$total_descuento = 0;

foreach ($ofertas_seleccionadas as $oferta_id) {

    $productos_oferta = ProductoService::getProductosDeOferta($oferta_id);

    $veces = PHP_INT_MAX;

    foreach ($productos_oferta as $po) {
        $id = $po->getId();
        $req = $po->cantidad;

        if (!isset($pedido_productos[$id])) {
            $veces = 0;
            break;
        }

        $veces = min($veces, intdiv($pedido_productos[$id], $req));
    }

    if ($veces <= 0) continue;

    // 🔹 precio del pack
    $precio_pack = 0;

    foreach ($productos_oferta as $po) {
        $precio_pack += $po->getPrecioFinal() * $po->cantidad;
    }

    $oferta = OfertaService::getById($oferta_id);

    $descuento_unitario = $precio_pack * ($oferta->getDescuento() / 100);

    $descuento_total_oferta = $descuento_unitario * $veces;

    $total_descuento += $descuento_total_oferta;

    // 🔹 guardar relación
    Pedido::guardarOferta(
        $pedido_id,
        $oferta_id,
        $veces,
        $descuento_total_oferta
    );

    // 🔹 RESTAR productos usados
    foreach ($productos_oferta as $po) {
        $id = $po->getId();
        $pedido_productos[$id] -= $po->cantidad * $veces;
    }
}

//////////////////////////////////////////////////////
// 🔹 4. Guardar totales
//////////////////////////////////////////////////////

Pedido::actualizarTotales(
    $pedido_id,
    $precio_sin_descuento,
    $total_descuento
);

//////////////////////////////////////////////////////
// 🔹 5. Redirigir
//////////////////////////////////////////////////////

header("Location: verPedido.php?id=" . $pedido_id);
exit;