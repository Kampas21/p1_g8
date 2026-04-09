<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';

class FormularioActualizarLineaPedido extends Formulario {

    private $pedido_id;
    private $producto_id;
    private $cantidad_actual;

    public function __construct(int $pedido_id, int $producto_id, int $cantidad_actual) {
        $this->pedido_id = $pedido_id;
        $this->producto_id = $producto_id;
        $this->cantidad_actual = $cantidad_actual;
        
        parent::__construct("formUpdateLinea_{$producto_id}", [
            'urlRedireccion' => 'carrito.php',
            'class' => 'inline-form'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        return <<<EOF
            <input type="hidden" name="producto_id" value="{$this->producto_id}">
            <input type="number" name="cantidad" value="{$this->cantidad_actual}" min="0" style="width:54px;padding:4px;">
            <button type="submit" class="btn small">OK</button>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $prod_id = (int)($datos['producto_id'] ?? 0);
        $cantidad = (int)($datos['cantidad'] ?? 1);
        
        if ($prod_id > 0) {
            if ($cantidad <= 0) {
                \Pedido::removeProducto($this->pedido_id, $prod_id);
                flash_set('success', 'Producto eliminado del carrito.');
            } else {
                \Pedido::updateCantidad($this->pedido_id, $prod_id, $cantidad);
                flash_set('success', 'Cantidad actualizada.');
            }
        }
    }
}