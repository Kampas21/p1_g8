<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';

class FormularioAccionesCocina extends Formulario {

    private $pedido_id;
    private $accion;       // 'tomar', 'marcar_plato', 'finalizar'
    private $producto_id;  // (Solo para 'marcar_plato')
    private $cocinero_id;

    public function __construct(int $pedido_id, string $accion, int $cocinero_id, int $producto_id = 0) {
        $this->pedido_id = $pedido_id;
        $this->accion = $accion;
        $this->cocinero_id = $cocinero_id;
        $this->producto_id = $producto_id;
        
        $formId = "formCocina_{$accion}_{$pedido_id}";
        if ($accion === 'marcar_plato') { $formId .= "_{$producto_id}"; }

        parent::__construct($formId, [
            'urlRedireccion' => 'panel_cocinero.php',
            'class' => 'inline-form'
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        $html = "<input type='hidden' name='accion' value='{$this->accion}'>";

        if ($this->accion === 'tomar') {
            $html .= "<button type='submit' class='btn primary'>🍳 Tomar Pedido</button>";
        } elseif ($this->accion === 'marcar_plato') {
            $html .= "<button type='submit' class='btn small primary'>Marcar Listo</button>";
        } elseif ($this->accion === 'finalizar') {
            $html .= "<button type='submit' class='btn success btn-bloque'>🛎️ Finalizar Pedido (Mandar a Listo Cocina)</button>";
        }

        return $html;
    }

    protected function procesaFormulario(&$datos) {
        $accion = $datos['accion'] ?? '';

        if ($accion === 'tomar') {
            \Pedido::asignarCocineroYEstado($this->pedido_id, $this->cocinero_id, 'cocinando');
        } elseif ($accion === 'marcar_plato') {
            \Pedido::marcarProductoPreparado($this->pedido_id, $this->producto_id);
        } elseif ($accion === 'finalizar') {
            \Pedido::cambiarEstado($this->pedido_id, 'listo_cocina');
        }
    }
}