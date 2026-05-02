<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

class FormularioEliminarLineaPedido extends Formulario {

    private $producto_id;

    public function __construct($producto_id) {
        parent::__construct('formRemoveLinea_' . $producto_id);
        $this->producto_id = $producto_id;
    }

    protected function generaCamposFormulario(&$datos) {

        return <<<HTML
        <input type="hidden" name="producto_id" value="{$this->producto_id}">
        <button type="submit">Eliminar</button>
HTML;
    }

    protected function procesaFormulario(&$datos) {

        $producto_id = trim($datos['producto_id'] ?? '');

        if (empty($producto_id)) {
            return;
        }

        \PedidoService::eliminarProductoDelCarrito($producto_id);

        $ofertas = $_SESSION['ofertas_seleccionadas'] ?? [];

        $errores = \OfertaService::aplicarOfertas($ofertas);

        $_SESSION['errores_ofertas'] = $errores;

        header("Location: " . RUTA_APP . "/vistas/pedidos/carrito.php");
        exit;
    }
}
