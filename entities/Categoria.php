<?php

class Categoria {

    private $id;
    private $nombre;
    private $descripcion;
    private $imagen;
    private $activa;

    public function __construct($id, $nombre, $descripcion,$imagen, $activa) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
        $this->activa = $activa;
    }

    // GETTERS

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }
    
    public function getImagen() {
        return $this->imagen;
    }

    public function isActiva() {
        return $this->activa;
    }


    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setImagen($imagen) {
        $this->imagen = $imagen;
    }
    public function setActiva($activa) {
        $this->activa = $activa;
    }
}