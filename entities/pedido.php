<?php

class Pedido {

    private $id;
    private $numero_pedido;
    private $fecha_hora;
    private $fecha;
    private $estado;
    private $tipo;
    private $metodo_pago;
    private $usuario_id;
    private $total_sin_descuentos;
    private $total_descuento;
    private $cocinero_id;

    public function __construct($id,$numero_pedido,$fecha_hora,$fecha,$estado,$tipo,$metodo_pago,$usuario_id,$total_sin_descuentos,$total_descuento,$cocinero_id) {
        $this->id = $id;
        $this->numero_pedido = $numero_pedido;
        $this->fecha_hora = $fecha_hora;
        $this->fecha = $fecha;
        $this->estado = $estado;
        $this->tipo = $tipo;
        $this->metodo_pago = $metodo_pago;
        $this->usuario_id = $usuario_id;
        $this->total_sin_descuentos = $total_sin_descuentos;
        $this->total_descuento = $total_descuento;
        $this->cocinero_id = $cocinero_id;
    }

    public function getId() {
        return $this->id;
    }
    public function getNumero_pedido() {
        return $this->numero_pedido;
    }
    public function getFecha_hora() {
        return $this->fecha_hora;
    }
    public function getFecha() {
        return $this->fecha;
    }
    public function getEstado() {
        return $this->estado;
    }
    public function getTipo() {
        return $this->tipo;
    }
    public function getMetodo_pago() {
        return $this->metodo_pago;
    }
    public function getUsuario_id() {
        return $this->usuario_id;
    }
    public function getTotal_sin_descuentos() {
        return $this->total_sin_descuentos;
    }
    public function getTotal_descuento() {
        return $this->total_descuento;
    }
    public function getCocinero_id() {
        return $this->cocinero_id;
    }

    public function getTotal() {
        $total = $this->total_sin_descuentos - $this->total_descuento;
        return $total > 0 ? (float)$total : 0.0;
    }
}