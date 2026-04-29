<?php

class Oferta
{

    private $id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $descuento; // porcentaje, e.g. 20 = 20%

    public function __construct($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, float $descuento)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->descuento = $descuento;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    public function getDescuento()
    {
        return $this->descuento;
    }

    public function estaActiva()
    {
        $tz = new DateTimeZone('Europe/Madrid');

        $ahora = new DateTime('now', $tz);
        $inicio = new DateTime($this->fecha_inicio, $tz);
        $fin = new DateTime($this->fecha_fin, $tz);

        //var_dump($this->fecha_inicio, $this->fecha_fin, $ahora, $inicio, $fin);

        return $ahora >= $inicio && $ahora <= $fin;
    }

    public function aplicarDescuento(float $precioOriginal)
    {
        return round($precioOriginal * (1 - $this->descuento / 100), 2);
    }
}
