<?php
require_once __DIR__ . '/../includes/application.php';

class Pedido
{

    /**
     * Crea un pedido nuevo en estado 'nuevo' y devuelve su id.
     */
    public static function crearPedido($usuario_id, $tipo)
    {
        global $conn;
        $stmt = $conn->prepare(
            "INSERT INTO pedidos (usuario_id, tipo, estado) VALUES (?, ?, 'nuevo')"
        );
        $stmt->bind_param("is", $usuario_id, $tipo);
        $stmt->execute();
        return $conn->insert_id;
    }

    /**
     * Obtiene el pedido en estado 'nuevo' del usuario, o null si no tiene ninguno.
     */
    public static function getPedidoNuevo($usuario_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "SELECT * FROM pedidos WHERE usuario_id = ? AND estado = 'nuevo' LIMIT 1"
        );
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function addProducto($pedido_id, $producto_id, $precio_unitario)
    {
        global $conn;
        $stmt = $conn->prepare(
            "INSERT INTO productos_en_pedido (pedido_id, producto_id, precio_unitario, cantidad)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE cantidad = cantidad + 1"
        );
        $stmt->bind_param("iid", $pedido_id, $producto_id, $precio_unitario);
        return $stmt->execute();
    }

    /**
     * Devuelve los productos de un pedido con nombre e iva del producto.
     */
    public static function getProductosPedido($pedido_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "SELECT pep.*, p.nombre, p.iva
         FROM productos_en_pedido pep
         JOIN productos p ON p.id = pep.producto_id
         WHERE pep.pedido_id = ?"
        );
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     */
    public static function updateCantidad($pedido_id, $producto_id, $cantidad)
    {
        global $conn;
        $stmt = $conn->prepare(
            "UPDATE productos_en_pedido SET cantidad = ?
         WHERE pedido_id = ? AND producto_id = ?"
        );
        $stmt->bind_param("iii", $cantidad, $pedido_id, $producto_id);
        return $stmt->execute();
    }

    /**
     * Elimina un producto del carrito.
     */
    public static function removeProducto($pedido_id, $producto_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "DELETE FROM productos_en_pedido WHERE pedido_id = ? AND producto_id = ?"
        );
        $stmt->bind_param("ii", $pedido_id, $producto_id);
        return $stmt->execute();
    }

    /**
     * Cancela y elimina el pedido y sus líneas.
     */
    public static function cancelarPedido($pedido_id)
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM productos_en_pedido WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $pedido_id);
        return $stmt->execute();
    }


}