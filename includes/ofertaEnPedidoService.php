<?php
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../entities/ofertaEnPedido.php';

class OfertaEnPedido
{
    public static function addOferta($pedido_id, $oferta_id, $veces, $descuento_total)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO ofertas_en_pedido (pedido_id, oferta_id, veces_aplicada, descuento_total)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("iiid", $pedido_id, $oferta_id, $veces, $descuento_total);
        $stmt->execute();
        $stmt->close();
    }

    public static function getOfertasDePedido($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT oep.*, o.nombre
             FROM ofertas_en_pedido oep
             JOIN ofertas o ON o.id = oep.oferta_id
             WHERE oep.pedido_id = ?"
        );

        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $ofertas = [];

        while ($fila = $result->fetch_assoc()) {
            $ofertas[] = new OfertaEnPedido(
                $fila['id'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['categoria_id'],
                $fila['precio_base'],
                $fila['iva'],
                $fila['disponible'],
                $fila['ofertado']
            );
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

}