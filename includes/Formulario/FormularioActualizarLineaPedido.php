<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../includes/ofertaService.php';

class FormularioActualizarLineaPedido extends Formulario {

    private $linea_id;
    private $cantidad_actual;

    public function __construct(int $linea_id, int $cantidad_actual) {
        $this->linea_id = $linea_id;
        $this->cantidad_actual = $cantidad_actual;
        
        parent::__construct("formUpdateLinea_{$linea_id}", [
            'urlRedireccion' => 'carrito.php',
            'class' => 'inline-form'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        return <<<EOF
            <input type="hidden" name="linea_id" value="{$this->linea_id}">
            <input type="number" name="cantidad" value="{$this->cantidad_actual}" min="0" class="input-cantidad">
            <button type="submit" class="btn small">OK</button>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $linea_id = (int)($datos['linea_id'] ?? 0);
        $cantidad = (int)($datos['cantidad'] ?? 1);
        
        if ($linea_id > 0) {
            if ($cantidad <= 0) {
                \PedidoService::eliminarProductoDelCarrito($linea_id);
                flash_set('success', 'Producto eliminado del carrito.');
            } else {
                \PedidoService::actualizarCantidadCarrito($linea_id, $cantidad);
                flash_set('success', 'Cantidad actualizada.');
            }
        }

        

        $ofertas = $_SESSION['ofertas_seleccionadas'] ?? [];

        $errores = \OfertaService::aplicarOfertas($ofertas);

        $_SESSION['errores_ofertas'] = $errores;
    }
}
