<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/util.php';

class FormularioAddCarrito extends Formulario {

    private $pedido_id;
    private $producto_id;
    private $categoria_id;

    public function __construct(int $pedido_id, int $producto_id, int $categoria_id) {
        $this->pedido_id = $pedido_id;
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
            $producto = \ProductoService::getById($prod_id);
            if ($producto) {
                $precio = $producto->getPrecioFinal();
                \Pedido::addProducto($this->pedido_id, $prod_id, $precio);
                flash_set('success', 'Producto añadido al carrito.');
            } else {
                return ['general' => 'El producto no existe.'];
            }
        }
    }
}