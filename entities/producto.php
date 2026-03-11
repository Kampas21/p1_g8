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

    public static function crearProducto($nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado, $imagen)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO productos 
            (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado, imagen) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("ssidiiis", $nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado, $imagen);
        return $stmt->execute();
    }

    public static function editarProducto($id, $nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado, $imagen)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE productos 
             SET nombre = ?, descripcion = ?, categoria_id = ?, precio_base = ?, iva = ?, disponible = ?, ofertado = ?, imagen = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ssidiiisi", $nombre, $descripcion, $categoria_id, $precio_base, $iva, $disponible, $ofertado, $imagen, $id);
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

public static function desactivarProductosPorCategoria($categoria_id)
{
    global $conn;

    $stmt = $conn->prepare(
        "UPDATE productos SET ofertado = 0 WHERE categoria_id = ?"
    );

    $stmt->bind_param("i", $categoria_id);
    return $stmt->execute();
}

    public static function getPrecioFinal($precio_base, $iva)
    {
        $precio_base = (float) $precio_base;
        $iva = (float) $iva;

        $precio_final = $precio_base * (1 + $iva / 100);

        return round($precio_final, 2);
    }
public static function getProductosConCategoria()
{
    global $conn;

    $query = "SELECT p.*, COALESCE(c.nombre, 'Sin categoría') AS categoria_nombre
              FROM productos p
              LEFT JOIN categorias c ON p.categoria_id = c.id
              ORDER BY p.id ASC";
    $rs = $conn->query($query);

    $productos = [];
    while ($fila = $rs->fetch_assoc()) {
        $productos[] = $fila;
    }

    return $productos;
}
    public static function getProductosPorCategoria($categoria_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre 
             FROM productos p
             JOIN categorias c ON p.categoria_id = c.id
             WHERE p.categoria_id = ?"
        );

        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $productos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila;
        }

        return $productos;
    }

    public static function getProductosByCategoria($categoria_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT * FROM productos WHERE categoria_id = ? AND disponible = 1"
        );
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function desvincularYDesofertarProductosPorCategoria($categoria_id)
{
    global $conn;

    $stmt = $conn->prepare(
        "UPDATE productos 
         SET ofertado = 0
         WHERE categoria_id = ?"
    );

    $stmt->bind_param("i", $categoria_id);
    return $stmt->execute();
}

public static function activarProductosPorCategoria($categoria_id)
{
    global $conn;

    $stmt = $conn->prepare(
        "UPDATE productos SET ofertado = 1 WHERE categoria_id = ?"
    );

    $stmt->bind_param("i", $categoria_id);
    return $stmt->execute();
}

}