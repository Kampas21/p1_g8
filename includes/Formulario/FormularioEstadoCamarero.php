<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioEstadoCamarero extends Formulario {

    private $pedido_id;
    private $accion;

    public function __construct($pedido_id, $accion) {
        $this->pedido_id = $pedido_id;
        $this->accion = $accion;

        parent::__construct('formCamarero_' . $pedido_id);
    }

 protected function generaCamposFormulario(&$datos) {

    return "
        <input type='hidden' name='pedido_id' value='{$this->pedido_id}'>
        <input type='hidden' name='accion' value='{$this->accion}'>
        <button type='submit' class='btn primary'>
            " . ($this->accion === 'cobrar' ? '💰 Cobrar' : '📦 Entregar') . "
        </button>
    ";
}

    protected function procesaFormulario(&$datos) {

    $pedido_id = filter_var($datos['pedido_id'], FILTER_VALIDATE_INT);
    $accion = $datos['accion'] ?? null;

    if (!$pedido_id || !$accion) {
        return;
    }

  

    if ($accion === 'cobrar') {
        \PedidoService::cambiarEstado($pedido_id, 'en_preparacion');
    }

    if ($accion === 'entregar') {
        \PedidoService::cambiarEstado($pedido_id, 'entregado');
    }

header("Location: " . RUTA_APP . "/vistas/pedidos/gestionCamarero.php");    exit;
}
}
