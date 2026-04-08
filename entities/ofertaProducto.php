<?php
require_once __DIR__ . '/../includes/application.php';

class OfertaProducto
{
    public static function getProductosDeOferta($oferta_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT op.*, p.nombre, p.precio_base, p.iva
             FROM oferta_productos op
             JOIN productos p ON p.id = op.producto_id
             WHERE op.oferta_id = ?"
        );

        $stmt->bind_param("i", $oferta_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function addProducto($oferta_id, $producto_id, $cantidad)
    {
       global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO oferta_productos (oferta_id, producto_id, cantidad)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)"
        );
        $stmt->bind_param("iii", $oferta_id, $producto_id, $cantidad);
        return $stmt->execute();
    }

    public static function removeProducto($oferta_id, $producto_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "DELETE FROM oferta_productos WHERE oferta_id = ? AND producto_id = ?"
        );
        $stmt->bind_param("ii", $oferta_id, $producto_id);
        return $stmt->execute();
    }

    public static function removeProductosDeOferta($oferta_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "DELETE FROM oferta_productos WHERE oferta_id = ?"
        );

        $stmt->bind_param("i", $oferta_id);
        return $stmt->execute();
    }
}
