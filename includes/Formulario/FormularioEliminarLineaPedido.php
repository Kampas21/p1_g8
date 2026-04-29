<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioEliminarLineaPedido extends Formulario {

    private $linea_id;

    public function __construct($linea_id) {
         parent::__construct('formRemoveLinea_' . $linea_id, ['urlRedireccion' => RUTA_APP . '/vistas/pedidos/carrito.php']);
        $this->linea_id = $linea_id;
    }

    protected function generaCamposFormulario(&$datos) {

        return <<<HTML
        <input type="hidden" name="linea_id" value="{$this->linea_id}">
        <button type="submit" class="btn danger small">Eliminar</button>
HTML;
    }

    protected function procesaFormulario(&$datos) {
        $linea_id = filter_var($datos['linea_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$linea_id) {
            return;
        }
        \PedidoService::removeProductoByLinea($linea_id);
        flash_set('success', 'Línea eliminada del carrito.');
    }
}
