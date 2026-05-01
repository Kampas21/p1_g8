<?php

class Recompensa {
    private $id;
    private $producto_id;
    private $bistrocoins;
    private $activa;
    private $producto_nombre;
    private $producto_descripcion;
    private $producto_precio_final;
    private $producto_imagen;

    public function __construct($id, $producto_id, $bistrocoins, $activa = 1, $producto_nombre = '', $producto_descripcion = '', $producto_precio_final = 0.0, $producto_imagen = null) {
        $this->id = $id;
        $this->producto_id = $producto_id;
        $this->bistrocoins = $bistrocoins;
        $this->activa = $activa;
        $this->producto_nombre = $producto_nombre;
        $this->producto_descripcion = $producto_descripcion;
        $this->producto_precio_final = (float)$producto_precio_final;
        $this->producto_imagen = $producto_imagen;
    }

    public function getId() { return $this->id; }
    public function getProductoId() { return $this->producto_id; }
    public function getBistrocoins() { return $this->bistrocoins; }
    public function isActiva() { return (int)$this->activa === 1; }
    public function getProductoNombre() { return $this->producto_nombre; }
    public function getProductoDescripcion() { return $this->producto_descripcion; }
    public function getProductoPrecioFinal() { return $this->producto_precio_final; }
    public function getProductoImagen() { return $this->producto_imagen; }
}
