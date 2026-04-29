<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioActualizarLineaPedido extends Formulario {

    private $producto_id;
    private $cantidad_actual;

    public function __construct(int $producto_id, int $cantidad_actual) {
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
            <input type="number" name="cantidad" value="{$this->cantidad_actual}" min="0" class="input-cantidad">
            <button type="submit" class="btn small">OK</button>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $prod_id = (int)($datos['producto_id'] ?? 0);
        $cantidad = (int)($datos['cantidad'] ?? 1);
        
        if ($prod_id > 0) {
            if ($cantidad <= 0) {
                \PedidoService::eliminarProductoDelCarrito($prod_id);
                flash_set('success', 'Producto eliminado del carrito.');
            } else {
                \PedidoService::actualizarCantidadCarrito($prod_id, $cantidad);
                flash_set('success', 'Cantidad actualizada.');
            }
        }
    }
}
