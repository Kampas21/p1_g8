<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

class FormularioAccionesCamarero extends Formulario {

    private $pedido_id;
    private $accion;
    private $producto_id;
    private $camarero_id;

    public function __construct($pedido_id, $accion, $camarero_id = 0, $producto_id = 0) {
        $this->pedido_id = $pedido_id;
        $this->accion = $accion;
        $this->camarero_id = (int)$camarero_id;
        $this->producto_id = (int)$producto_id;

        parent::__construct('formCamarero_' . $pedido_id . '_' . $accion . ($this->producto_id ? '_' . $this->producto_id : ''));
    }

 protected function generaCamposFormulario(&$datos) {

    return "
        <input type='hidden' name='pedido_id' value='{$this->pedido_id}'>
        <input type='hidden' name='accion' value='{$this->accion}'>
        <input type='hidden' name='producto_id' value='{$this->producto_id}'>
        <input type='hidden' name='camarero_id' value='{$this->camarero_id}'>
        <button type='submit' class='btn primary'>
            " . ($this->accion === 'cobrar' ? '💰 Cobrar' : ($this->accion === 'preparar_linea' ? '🟡 Preparar' : ($this->accion === 'pasar_entrega' ? '🛎️ Pasar a entrega' : '📦 Entregar'))) . "
        </button>
    ";
}

    protected function procesaFormulario(&$datos) {

    $pedido_id = filter_var($datos['pedido_id'], FILTER_VALIDATE_INT);
    $accion = $datos['accion'] ?? null;
    $producto_id = filter_var($datos['producto_id'] ?? null, FILTER_VALIDATE_INT);
    $camarero_id = filter_var($datos['camarero_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$pedido_id || !$accion) {
        return;
    }

    // Si tenemos un camarero, lo asignamos al pedido
    if ($camarero_id) {
        \PedidoService::asignarCamarero($pedido_id, $camarero_id);
    }

    if ($accion === 'cobrar') {
        $ok = \PedidoService::cambiarEstado($pedido_id, 'en_preparacion');
        if (!$ok) {
            flash_set('error', 'El cliente no tiene BistroCoins suficientes para procesar el pedido. Cancela el pedido o cobra el importe íntegro.');
        } else {
            flash_set('success', 'Pedido cobrado y enviado a cocina.');
        }
    }

    if ($accion === 'preparar_linea' && $producto_id) {
        \PedidoService::marcarProductoPreparadoCamarero($pedido_id, $producto_id);
    }

    if ($accion === 'pasar_entrega') {
        \PedidoService::terminarPedidoParaEntrega($pedido_id);
    }

    if ($accion === 'entregar') {
        \PedidoService::terminarPedidoParaEntrega($pedido_id);
        \PedidoService::cambiarEstado($pedido_id, 'entregado');
    }

    $tab = ($accion === 'entregar') ? 'entregar' : (($accion === 'cobrar') ? 'recibidos' : 'listos');
    header("Location: " . RUTA_APP . "/vistas/pedidos/gestionCamarero.php?tab=" . $tab);
    exit;
}
}
