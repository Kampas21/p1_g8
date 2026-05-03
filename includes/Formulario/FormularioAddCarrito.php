<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

class FormularioAddCarrito extends Formulario {

    private $producto_id;
    private $categoria_id;

    public function __construct(int $producto_id, int $categoria_id) {
        $this->producto_id = $producto_id;
        $this->categoria_id = $categoria_id;
        
        $urlRedireccion = 'catalogo.php' . ($categoria_id ? "?categoria={$categoria_id}" : "");
        // Generamos un ID único por formulario 
        parent::__construct("formAddCarrito_{$producto_id}", [
            'urlRedireccion' => $urlRedireccion,
            'class' => 'inline-form' 
        ]);
    }

    protected function generaCamposFormulario(&$datos) {
        // En este caso, el formulario es simplemente un par de hidden inputs y un botón Submit:
        return <<<EOF
            <input type="hidden" name="producto_id" value="{$this->producto_id}">
            <input type="hidden" name="categoria_id" value="{$this->categoria_id}">
            <button type="submit" class="btn primary small">+ Añadir</button>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $prod_id = (int)($datos['producto_id'] ?? 0);
        
        if ($prod_id > 0) {
            $producto = \ProductoDAO::getById($prod_id);
            if ($producto) {
                $precio = $producto->getPrecioFinal();
                \PedidoService::agregarProductoAlCarrito($prod_id, $precio);
                flash_set('success', 'Producto añadido al carrito.');
            } else {
                return ['general' => 'El producto no existe.'];
            }
        }
    }
}
