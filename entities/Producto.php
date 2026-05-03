<?php

class Producto {

    private $id;
    private $nombre;
    private $descripcion;
    private $categoria_id;
    private $precio;
    private $iva;
    private $disponible;
    private $ofertado;
    private $imagen;
    private $se_cocina;

    public $cantidad = 0;

    public function __construct($id, $nombre, $descripcion, $categoria_id, $precio, $iva, $disponible, $ofertado, $imagen = null, $se_cocina = 1) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->categoria_id = $categoria_id;
        $this->precio = $precio;
        $this->iva = $iva;
        $this->disponible = $disponible;
        $this->ofertado = $ofertado;
        $this->imagen = $imagen;
        $this->se_cocina = $se_cocina;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getCategoriaId() {
        return $this->categoria_id;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getIVA() {
        return $this->iva;
    }

    public function isDisponible() {
        return $this->disponible;
    }

    public function isOfertado() {
        return $this->ofertado;
    }

    public function getPrecioFinal() {
        return round($this->precio * (1 + $this->iva / 100), 2);
    }

    public function getImagen() {
        return $this->imagen;
    }

    public function getSeCocina() {
        return $this->se_cocina;
    }
}