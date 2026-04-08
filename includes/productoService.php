<?php
require_once __DIR__ . '/../entities/Producto.php';
require_once __DIR__ . '/db.php';

class ProductoService {

  public static function getAllByCategoria($categoria_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM productos WHERE categoria_id = ?");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $productos = [];

    while ($fila = $result->fetch_assoc()) {
        $productos[] = $fila;
    }

    return $productos;
}

    public static function getById($id) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return new Producto(
            $row['id'],
            $row['nombre'],
            $row['descripcion'],
            $row['categoria_id'],
            $row['precio'],
            $row['iva'],
            $row['disponible'],
            $row['ofertado']
        );
    }

    public static function crear($nombre, $descripcion, $categoria_id, $precio, $iva) {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, categoria_id, precio, iva, disponible, ofertado) VALUES (?, ?, ?, ?, ?, 1, 1)");
        $stmt->bind_param("ssidd", $nombre, $descripcion, $categoria_id, $precio, $iva);
        $stmt->execute();
    }

    public static function actualizar($id, $nombre, $descripcion, $categoria_id, $precio, $iva) {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, categoria_id=?, precio=?, iva=? WHERE id=?");
        $stmt->bind_param("ssiddi", $nombre, $descripcion, $categoria_id, $precio, $iva, $id);
        $stmt->execute();
    }

    public static function desactivar($id) {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos SET ofertado = 0 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public static function activar($id) {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos SET ofertado = 1 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

    }

    public static function create($nombre, $descripcion, $categoria_id, $precio, $iva) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO productos (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado)
        VALUES (?, ?, ?, ?, ?, 1, 0)
    ");

    $stmt->bind_param("ssidi", $nombre, $descripcion, $categoria_id, $precio, $iva);
    $stmt->execute();
}
}