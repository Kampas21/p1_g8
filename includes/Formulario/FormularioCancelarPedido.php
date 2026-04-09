<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioCancelarPedido extends Formulario {

    private $pedido_id;

    public function __construct(int $pedido_id) {
        $this->pedido_id = $pedido_id;
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
        \PedidoService::cancelarPedido($this->pedido_id);
        flash_set('success', 'Pedido cancelado.');
    }
}