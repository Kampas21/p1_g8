<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido = PedidoService::getPedidoNuevo($usuario_id);

if (!$pedido) {
    header("Location: ../../vistas/pedidos/elegirTipo.php");
    exit;
}

$pedido_id = $pedido->getId();

$producto_id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

if (!$producto_id || !$cantidad || $cantidad < 1) {
    header("Location: ../../vistas/catalogo.php");
    exit;
}

$producto = ProductoDAO::getById($producto_id);

if (!$producto) {
    header("Location: ../../vistas/catalogo.php");
    exit;
}

$precio = $producto->getPrecio();

for ($i = 0; $i < $cantidad; $i++) {
    PedidoService::addProducto($pedido_id, $producto_id, $precio);
}

header("Location: ../../vistas/pedidos/carrito.php");
exit;