<?php

class Productos_Pedido {

    private $id;
    private $nombre;
    private $pedido_id;
    private $producto_id;
    private $precio;
    private $cantidad;
    private $estado;

    private $imagen;

    

    public function __construct($id, $nombre, $pedido_id, $producto_id, $precio, $cantidad, $estado) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pedido_id = $pedido_id;
        $this->producto_id = $producto_id;
        $this->precio = $precio;
        $this->cantidad = $cantidad;
        $this->estado = $estado;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getPedido_id() {
        return $this->pedido_id;
    }

    public function getProductoId() {
        return $this->producto_id;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getImagen(){
        return null;
    }
}