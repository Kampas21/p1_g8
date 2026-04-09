<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';

class FormularioElegirTipo extends Formulario {

    private $usuario_id;

    public function __construct(int $usuario_id) {
        $this->usuario_id = $usuario_id;
        parent::__construct('formTipo', ['urlRedireccion' => 'catalogo.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        return <<<EOF
        <div class="actions-inline mt-20">
            <button type="submit" name="tipo" value="local" class="btn primary">Tomar en el local</button>
            <button type="submit" name="tipo" value="llevar" class="btn primary">Para llevar</button>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $tipo = $_POST['tipo'] ?? 'local';
        
        $pedido_id = \Pedido::crearPedido($this->usuario_id, $tipo);
        $_SESSION['ultimo_pedido_id'] = $pedido_id;
    }
}
