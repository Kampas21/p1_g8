<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

class FormularioElegirTipo extends Formulario {

    private $usuario_id;

    public function __construct($usuario_id) {
        parent::__construct('formElegirTipo');
        $this->usuario_id = $usuario_id;
    }

    protected function generaCamposFormulario(&$datos) {

    return <<<HTML
    <form method="POST">
        <div class="acciones-tipo">
            <button type="submit" name="tipo" value="local">🍽️ En local</button>
            <button type="submit" name="tipo" value="llevar">🥡 Para llevar</button>
        </div>
    </form>
HTML;
}

    protected function procesaFormulario(&$datos) {

    
        $tipo = $datos['tipo'] ?? null;

        if (!$tipo || !in_array($tipo, ['local', 'llevar'])) {
            $this->errores[] = "Tipo de pedido inválido";
            return;
        }

        \PedidoService::iniciarCarrito($tipo);

        header("Location: " . RUTA_APP . "/vistas/pedidos/catalogo.php");
        exit;
    }
}
