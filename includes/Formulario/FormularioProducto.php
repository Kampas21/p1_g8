<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/config.php'; 

class FormularioProducto extends Formulario
{
    private $isCreate;
    private $productoToEdit;
    private $categoriaId;

    public function __construct(bool $isCreate, $categoriaId, ?\Producto $productoToEdit = null) {
        $this->isCreate = $isCreate;
        $this->productoToEdit = $productoToEdit;
        $this->categoriaId = $categoriaId;

        parent::__construct('formProducto', [
            'urlRedireccion' => RUTA_APP.'/vistas/productos/mostrarProductosCategoria.php?id=' . $categoriaId
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombre = $datos['nombre'] ?? ($this->productoToEdit ? $this->productoToEdit->getNombre() : '');
        $descripcion = $datos['descripcion'] ?? ($this->productoToEdit ? $this->productoToEdit->getDescripcion() : '');
        $precio = $datos['precio'] ?? ($this->productoToEdit ? $this->productoToEdit->getPrecio() : '');
        $iva = $datos['iva'] ?? ($this->productoToEdit ? $this->productoToEdit->getIVA() : '21');

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['nombre', 'descripcion', 'precio', 'iva'], $this->errores, 'span', array('class' => 'texto-error'));

        $sel4 = ($iva == 4) ? 'selected' : '';
        $sel10 = ($iva == 10) ? 'selected' : '';
        $sel21 = ($iva == 21) ? 'selected' : '';

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Datos del Producto</legend>
            <div class="mt-10">
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" required class="w-100" />
                {$erroresCampos['nombre']}
            </div>
            <div class="mt-10">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="5" class="w-100" required>$descripcion</textarea>
                {$erroresCampos['descripcion']}
            </div>
            <div class="mt-10">
                <label for="precio">Precio Base (€):</label>
                <input id="precio" type="number" step="0.01" name="precio" value="$precio" required class="w-100" />
                {$erroresCampos['precio']}
            </div>
            <div class="mt-10">
                <label for="iva">IVA (%):</label>
                <select id="iva" name="iva" required class="w-100">
                    <option value="4" $sel4>4%</option>
                    <option value="10" $sel10>10%</option>
                    <option value="21" $sel21>21%</option>
                </select>
                {$erroresCampos['iva']}
            </div>
            <div class="mt-20">
                <button type="submit" class="btn primary">Guardar Producto</button>
            </div>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $precio = trim($datos['precio'] ?? '');
        $iva = trim($datos['iva'] ?? '');

        if ($nombre === '') {
            $this->errores['nombre'] = 'El nombre no puede estar vacío.';
        }
        if ($descripcion === '') {
            $this->errores['descripcion'] = 'La descripción no puede estar vacía.';
        }
        if ($precio === '' || !is_numeric($precio) || $precio < 0) {
            $this->errores['precio'] = 'El precio debe ser un número positivo.';
        }
        if (!in_array($iva, ['4', '10', '21'])) {
            $this->errores['iva'] = 'El IVA seleccionado no es válido.';
        }

        if (count($this->errores) === 0) {
            if ($this->isCreate) {
                \ProductoService::create($nombre, $descripcion, $this->categoriaId, (float)$precio, (int)$iva);
            } else {
                \ProductoService::actualizar($this->productoToEdit->getId(), $nombre, $descripcion, $this->categoriaId, (float)$precio, (int)$iva);
            }
        }
    }
}