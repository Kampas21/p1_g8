<?php

class OfertaProducto
{

    private $id;
    private $oferta_id;
    private $producto_id;
    private $cantidad;

    public function __construct($id, $oferta_id, $producto_id, $cantidad)
    {
        $this->id = $id;
        $this->oferta_id = $oferta_id;
        $this->producto_id = $producto_id;
        $this->cantidad = $cantidad;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getOfertaId()
    {
        return $this->oferta_id;
    }

    public function getProductoId()
    {
        return $this->producto_id;
    }

    public function getCantidadId()
    {
        return $this->cantidad;
    }
}

