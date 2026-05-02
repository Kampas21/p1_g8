<?php

class ProductoPedido {

    private $id;
    private $nombre;
    private $pedido_id;
    private $producto_id;
    private $precio;
    private $cantidad;
    private $estado;
    private $imagen;   
    private $se_cocina;
    private $es_recompensa;
    private $bistrocoins_unitarios;
    

    public function __construct($id, $nombre, $pedido_id, $producto_id, $precio, $cantidad, $estado, $imagen = null, $se_cocina = 1, $es_recompensa = 0, $bistrocoins_unitarios = 0) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pedido_id = $pedido_id;
        $this->producto_id = $producto_id;
        $this->precio = $precio;
        $this->cantidad = $cantidad;
        $this->estado = $estado;
        $this->imagen = $imagen;
        $this->se_cocina = $se_cocina;
        $this->es_recompensa = (int)$es_recompensa;
        $this->bistrocoins_unitarios = (int)$bistrocoins_unitarios;
    }

    public function getSeCocina() {
        return $this->se_cocina;
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
        return $this->imagen;
    }

    public function esRecompensa(): bool { 
        return $this->es_recompensa === 1; 
    }

    public function getBistrocoinsUnitarios(): int { 
        return $this->bistrocoins_unitarios; 
    }

    public function getBistrocoinsTotales(): int { 
        return $this->bistrocoins_unitarios * $this->cantidad; 
    }
}