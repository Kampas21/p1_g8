<?php
require_once __DIR__ . '/../includes/application.php';

class Producto
{
    public static function getProductos()
    {
        global $conn;

        $query = "SELECT * FROM productos";
        $rs = $conn->query($query);

        $productos = [];

        while ($fila = $rs->fetch_assoc()) {
            $productos[] = $fila;
        }

        return $productos;
    }

    public static function getProductoById($id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public static function crearProducto($nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO productos 
            (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado) 
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("ssidiii", $nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado);
        return $stmt->execute();
    }

    public static function editarProducto($id, $nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE productos SET nombre = ?, descripcion = ?, categoria_id  = ?, precio_base  = ?, iva  = ?, disponible  = ?, ofertado  = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ssidiiii", $nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado, $id);
        return $stmt->execute();
    }

    public static function borrarProducto($id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE productos SET ofertado = 0 WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function activarProducto($id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE productos SET ofertado = 1 WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function getPrecioFinal($precio_base, $iva)
    {
        $precio_base = (float) $precio_base;
        $iva = (float) $iva;

        $precio_final = $precio_base * (1 + $iva / 100);

        return round($precio_final, 2);
    }
}
