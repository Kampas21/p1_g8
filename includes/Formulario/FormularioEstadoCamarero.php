<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioEstadoCamarero extends Formulario {

    private $pedido_id;
    private $accion;
    private $btnLabel;

    public function __construct(int $pedido_id, string $accion, string $btnLabel) {
        $this->pedido_id = $pedido_id;
        $this->accion = $accion;
        $this->btnLabel = $btnLabel;
        
        parent::__construct("formCamarero_{$accion}_{$pedido_id}", [
            'urlRedireccion' => 'gestionCamarero.php',
            'class' => 'inline-form'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        return <<<EOF
            <input type="hidden" name="accion" value="{$this->accion}">
            <button type="submit" class="btn primary btn-bloque mt-10">{$this->btnLabel}</button>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $accion = $datos['accion'] ?? '';
        
        $transiciones = [
            'cobrar'    => ['de' => 'recibido',     'a' => 'en_preparacion'],
            'preparado' => ['de' => 'listo_cocina', 'a' => 'terminado'],
            'entregar'  => ['de' => 'terminado',    'a' => 'entregado'],
        ];

        if (isset($transiciones[$accion])) {
            $pedido = \PedidoService::getPedidoById($this->pedido_id);
            $t = $transiciones[$accion];
            if ($pedido && $pedido['estado'] === $t['de']) {
                \PedidoService::cambiarEstado($this->pedido_id, $t['a']);
            }
        }
    }
}
