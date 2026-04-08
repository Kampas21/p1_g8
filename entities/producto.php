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

    public function __construct($id, $nombre, $descripcion, $categoria_id, $precio, $iva, $disponible, $ofertado) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->categoria_id = $categoria_id;
        $this->precio = $precio;
        $this->iva = $iva;
        $this->disponible = $disponible;
        $this->ofertado = $ofertado;
    }

    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getCategoriaId() { return $this->categoria_id; }
    public function getPrecio() { return $this->precio; }
    public function getIVA() { return $this->iva; }
    public function isDisponible() { return $this->disponible; }
    public function isOfertado() { return $this->ofertado; }
}
?>