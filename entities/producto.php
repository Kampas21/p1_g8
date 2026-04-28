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

    public $cantidad = 0;

    public function __construct($id, $nombre, $descripcion, $categoria_id, $precio, $iva, $disponible, $ofertado, $imagen = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->categoria_id = $categoria_id;
        $this->precio = $precio;
        $this->iva = $iva;
        $this->disponible = $disponible;
        $this->ofertado = $ofertado;
        $this->imagen = $imagen;
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
}