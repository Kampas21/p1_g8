<?php
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../entities/ofertaProducto.php';

class OfertaProductoService
{
    public static function addProducto($oferta_id, $producto_id, $cantidad)
    {
       global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO oferta_productos (oferta_id, producto_id, cantidad)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)"
        );
        $stmt->bind_param("iii", $oferta_id, $producto_id, $cantidad);
        $stmt->execute();

        $insertId = $conn->insert_id;
        $stmt->close();

        return $insertId;
    }

    public static function removeProducto($oferta_id, $producto_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "DELETE FROM oferta_productos WHERE oferta_id = ? AND producto_id = ?"
        );
        $stmt->bind_param("ii", $oferta_id, $producto_id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    public static function removeProductosDeOferta($oferta_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "DELETE FROM oferta_productos WHERE oferta_id = ?"
        );

        $stmt->bind_param("i", $oferta_id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }
}