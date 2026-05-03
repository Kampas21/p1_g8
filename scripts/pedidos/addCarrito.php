<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/PedidoService.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';

$user = require_login();
if (!PedidoService::carritoTieneTipo()) {
    header("Location: ../../vistas/pedidos/elegirTipo.php");
    exit;
}

$producto_id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

if (!$producto_id || !$cantidad || $cantidad < 1) {
    header("Location: ../../vistas/pedidos/catalogo.php");
    exit;
}

$producto = ProductoDAO::getById($producto_id);

if (!$producto) {
    header("Location: ../../vistas/pedidos/catalogo.php");
    exit;
}

$precio = $producto->getPrecio();

PedidoService::agregarProductoAlCarrito($producto_id, $precio, $cantidad);

header("Location: ../../vistas/pedidos/carrito.php");
exit;