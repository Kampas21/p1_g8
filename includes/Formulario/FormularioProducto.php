<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../productoService.php';
class FormularioProducto extends Formulario {

    private $isCreate;
    private $categoria_id;
    private $producto;

    public function __construct($isCreate, $categoria_id, $producto = null) {
        parent::__construct('formProducto');

        $this->isCreate = $isCreate;
        $this->categoria_id = $categoria_id;
        $this->producto = $producto;
    }

    protected function generaCamposFormulario(&$datos) {

        $nombre = htmlspecialchars($datos['nombre'] ?? ($this->producto ? $this->producto->getNombre() : ''));
        $descripcion = htmlspecialchars($datos['descripcion'] ?? ($this->producto ? $this->producto->getDescripcion() : ''));
        $precio = $datos['precio'] ?? ($this->producto ? $this->producto->getPrecio() : '');
        $iva = $datos['iva'] ?? ($this->producto ? $this->producto->getIVA() : 21);

        $html = <<<HTML

        <p>
            <label>Nombre:</label><br>
            <input type="text" name="nombre" value="$nombre" required minlength="3">
        </p>

        <p>
            <label>Descripción:</label><br>
            <textarea name="descripcion" required minlength="3">$descripcion</textarea>
        </p>

        <p>
            <label>Precio base (€):</label><br>
            <input type="number" name="precio" step="0.01" min="0" value="$precio" required>
        </p>

        <p>
            <label>IVA:</label><br>
            <select name="iva" required>
                <option value="4" {$this->selected($iva, 4)}>4%</option>
                <option value="10" {$this->selected($iva, 10)}>10%</option>
                <option value="21" {$this->selected($iva, 21)}>21%</option>
            </select>
        </p>

        <input type="hidden" name="categoria_id" value="{$this->categoria_id}">

        <p>
            <button type="submit">
                {$this->textoBoton()}
            </button>
        </p>

HTML;

        return $html;
    }

    private function textoBoton() {
        return $this->isCreate ? "Crear producto" : "Actualizar producto";
    }

    private function selected($valor, $option) {
        return ((int)$valor === (int)$option) ? 'selected' : '';
    }

    protected function procesaFormulario(&$datos) {

        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $precio = floatval($datos['precio'] ?? -1);
        $iva = intval($datos['iva'] ?? 0);
        $categoria_id = intval($datos['categoria_id']);

        /* ========= VALIDACIONES ========= */

        if (!$nombre || strlen($nombre) < 3) {
            $this->errores['nombre'] = "Nombre inválido (mín 3 caracteres)";
        }

        if (!$descripcion || strlen($descripcion) < 3) {
            $this->errores['descripcion'] = "Descripción inválida";
        }

        if ($precio < 0) {
            $this->errores['precio'] = "Precio inválido";
        }

        if (!in_array($iva, [4,10,21])) {
            $this->errores['iva'] = "IVA inválido";
        }

        /* ========= SI HAY ERRORES → PARAR ========= */

        if (count($this->errores) > 0) {
            return;
        }

        /* ========= GUARDAR ========= */

        if ($this->isCreate) {
            \ProductoService::create($nombre, $descripcion, $categoria_id, $precio, $iva);
        } else {
            \ProductoService::update(
                $this->producto->getId(),
                $nombre,
                $descripcion,
                $categoria_id,
                $precio,
                $iva
            );
        }

        /* ========= REDIRECCIÓN ========= */

        $this->urlRedireccion = "mostrarProductosCategoria.php?id=" . $categoria_id;
    }
}