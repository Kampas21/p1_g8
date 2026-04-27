<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioEliminarLineaPedido extends Formulario {

    private $pedido_id;
    private $producto_id;

    public function __construct($pedido_id, $producto_id) {
        parent::__construct('formRemoveLinea_' . $producto_id);
        $this->pedido_id = $pedido_id;
        $this->producto_id = $producto_id;
    }

    protected function generaCamposFormulario(&$datos) {

        return <<<HTML
        <input type="hidden" name="producto_id" value="{$this->producto_id}">
        <button type="submit">Eliminar</button>
HTML;
    }

    protected function procesaFormulario(&$datos) {

        $producto_id = filter_var($datos['producto_id'], FILTER_VALIDATE_INT);

        if (!$producto_id) {
            return;
        }

        \PedidoService::removeProducto($this->pedido_id, $producto_id);

        header("Location: " . RUTA_APP . "/vistas/pedidos/carrito.php");
        exit;
    }
}
