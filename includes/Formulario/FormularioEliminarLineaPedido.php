<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';

class FormularioEliminarLineaPedido extends Formulario {

    private $pedido_id;
    private $producto_id;

    public function __construct(int $pedido_id, int $producto_id) {
        $this->pedido_id = $pedido_id;
        $this->producto_id = $producto_id;
        
        parent::__construct("formRemoveLinea_{$producto_id}", [
            'urlRedireccion' => 'carrito.php',
            'class' => 'inline-form'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        return <<<EOF
            <input type="hidden" name="producto_id" value="{$this->producto_id}">
            <button type="submit" class="btn danger small">Eliminar</button>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $prod_id = (int)($datos['producto_id'] ?? 0);
        if ($prod_id > 0) {
            \Pedido::removeProducto($this->pedido_id, $prod_id);
            flash_set('success', 'Producto eliminado.');
        }
    }
}