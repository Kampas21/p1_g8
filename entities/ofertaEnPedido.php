<?php

class OfertaProducto
{

    private $id;
    private $pedido_id;
    private $oferta_id;
    private $veces_aplicada;
    private $descuento_total;

    public function __construct($id, $pedido_id, $oferta_id, $veces_aplicada, $descuento_total) {
        $this->id = $id;
        $this->pedido_id = $pedido_id;
        $this->oferta_id = $oferta_id;
        $this->veces_aplicada = $veces_aplicada;
        $this->descuento_total = $descuento_total;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getPedidoId()
    {
        return $this->pedido_id;
    }

    public function getOfertaId()
    {
        return $this->oferta_id;
    }

    public function getVeces_aplicada()
    {
        return $this->veces_aplicada;
    }

    public function getDescuento_total()
    {
        return $this->descuento_total;
    }
}

