<?php
require_once __DIR__ . '/../includes/application.php';

class Oferta
{
    public static function getOfertas()
    {
        global $conn;

        $query = "SELECT * FROM ofertas ORDER BY fecha_inicio DESC";
        $rs = $conn->query($query);

        $ofertas = [];

        while ($fila = $rs->fetch_assoc()) {
            $ofertas[] = $fila;
        }

        return $ofertas;
    }

    public static function getOfertasActivas()
    {
        global $conn;

        $query = "SELECT * FROM ofertas 
                  WHERE NOW() BETWEEN fecha_inicio AND fecha_fin 
                  AND activa = 1";

        $rs = $conn->query($query);

        $ofertas = [];
        while ($fila = $rs->fetch_assoc()) {
            $ofertas[] = $fila;
        }

        return $ofertas;
    }

    public static function getOfertaById($id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM ofertas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function crearOferta($nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO ofertas (nombre, descripcion, fecha_inicio, fecha_fin, descuento)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("ssssd", $nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento);
        $stmt->execute();
        return $conn->insert_id;
    }

    public static function editarOferta($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE ofertas 
             SET nombre = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, descuento = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ssssdi", $nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento, $id);
        return $stmt->execute();
    }

    public static function borrarOferta($id)
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM ofertas WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function getProductosDeOferta($oferta_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "SELECT op.producto_id, p.nombre, op.cantidad, p.precio_base, p.iva
         FROM oferta_productos op
         JOIN productos p ON p.id = op.producto_id
         WHERE op.oferta_id = ?"
        );
        $stmt->bind_param("i", $oferta_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function ofertaEnUso($oferta_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT pedido_id
         FROM ofertas_en_pedido
         WHERE oferta_id = ?"
        );

        $stmt->bind_param("i", $oferta_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
