<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

class FormularioCancelarPedido extends Formulario {

    public function __construct() {
        parent::__construct("formCancelarPedido", ['urlRedireccion' => 'elegirTipo.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        return <<<EOF
            <button type="submit" class="btn danger" onclick="return confirm('¿Seguro que quieres cancelar el pedido?')">
                Cancelar pedido
            </button>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        \PedidoService::limpiarCarrito();
        unset($_SESSION['ultimo_pedido_id']);
        flash_set('success', 'Pedido cancelado.');
    }
}
